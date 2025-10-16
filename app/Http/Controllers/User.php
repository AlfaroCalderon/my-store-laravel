<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;
use App\Services\JWTService;
class User extends Controller
{
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

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => $user
            ], 201);
            //code...
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
            'password' => 'required|string|min:8'
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

            //Generate tokens
            $tokenPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ];

            $tokens = $jwtService->generateTokenPair($tokenPayload);

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
}
