<?php

namespace App\Http\Controllers;

use App\Services\WebHookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;
use App\Services\JWTService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Session;
class User extends Controller
{

    private function getGeolocation($ipaddress){
        try {
            $reponse = http::timeout(5)->get("https://api.ipquery.io/{$ipaddress}");

            if($reponse->successful()){
                $data = $reponse->json();
                return [
                    'country' => $data['location']['country'] ?? null,
                    'city' => $data['location']['city'] ?? null,
                    'latitude' => $data['location']['latitude'] ?? null,
                    'longitude' => $data['location']['longitude'] ?? null,
                ];
            }
        } catch (\Exception $error) {
        log::warning('Error when obtaining the Geolocation: '.$error->getMessage());
        }

        return [
                    'country' => null,
                    'city' =>  null,
                    'latitude' =>  null,
                    'longitude' =>  null,
               ];
    }


    public function register(Request $request){
        //We validate the data that we get from the request

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255|min:3',
            'lastname' => 'required|string|max:255|min:3',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:manager,user'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'validation error',
                'message' => $validator->errors()
            ],422);
        }

        try {

            $user = UserModel::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'is_active' => true,
                'login_attempts' => 0,
            ]);

            //Send welcome greeting
            $webHookService = new WebHookService();
            $webHookService->sendWelcomeEmail([
                'type' => 'welcome_new_user',
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $error) {
            return response()->json([
                'status' => 'database_error',
                'message' => $error->getMessage()
            ],500);
        }
    }


    public function login(Request $request, JWTService $jwtService){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8',
            'ip_public' => 'required|ip'
        ]);

        //Validation
        if($validator->fails()){
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $user = UserModel::where('email', $request->email)->first();

            //Check if the user exists
            if(!$user){
                return response()->json([
                    'status' => 'user_not_found',
                    'message' => 'Invalid credentials'
                ],401);
            }

            //Verify if the user is active
            if(!$user->is_active){
                return response()->json([
                    'status' => 'user_not_active',
                    'message' => 'Account is inactive. Please contact support'
                ]);
            }

            //Verify if the account is blocked due too many login attempts
            if($user->login_attempts >= 5){
                return response()->json([
                    'status' => 'account_blocked',
                    'message' => 'Account is blocked due to too many login attempts. Please contact support'
                ], 401);
            }

            //Check if the password is correct
            if(!Hash::check($request->password, $user->password)){
                //Increment login attempts
                $user->increment('login_attempts');
                return response()->json([
                    'status' => 'invalid_credentials',
                    'message' => 'Invalid credentials',
                    'attempts_remaining' => max(0, 5 - $user->login_attempts)
                ], 401);
            }

            //Reset login attempts
            $user->login_attempts = 0;
            $user->last_login = now();
            $user->save();

            //Register session geolocation
            $ip_public = $request->ip_public;
            $geoData = $this->getGeolocation($ip_public);

            //Save the data inside the table sessions
            Session::create([
                'user_id' => $user->id,
                'ip_address' => $ip_public,
                'country' => $geoData['country'],
                'city' => $geoData['city'],
                'latitude' => $geoData['latitude'],
                'longitude' => $geoData['longitude']
            ]);


            //Generate tokens
            $tokenPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'auth_role' => $user->role
            ];

            $tokens = $jwtService->generateTokenPair($tokenPayload);

             //Send welcome greeting
            $webHookService = new WebHookService();
            $webHookService->sendLoginSession([
                'type' => 'login_session',
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'country' => $geoData['country'],
                'city' => $geoData['city']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $tokens
            ],200);

        } catch (\Exception $error) {
            return response()->json([
                'status' => 'database_error',
                'errors' => $error->getMessage()
            ],500);
        }
    }

    public function getUserById(Request $request, $id){
        try {

            $sessions = UserModel::find($id)->sessions;

            //We search the user
            $user = UserModel::find($id);
            //We verify if the user exist
            if(!$user){
                return response()->json([
                    'status' => 'user_not_found',
                    'message' => 'User not found'
                ],400);
            }

            //Return the user info
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'last_login' => $user->last_login,
                    'login_attempts' => $user->login_attempts,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'sessions' => $sessions
                ]
            ],200);

        } catch (\Exception $error) {
            return response()->json([
                'status' => 'database_error',
                'errors' => $error->getMessage()
            ],500);
        }
    }
}
