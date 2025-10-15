<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;

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
}
