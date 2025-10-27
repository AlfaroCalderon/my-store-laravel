<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User;
/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/public', function(Request $request){
    return response()->json([
        'message' => 'Hello'
    ]);
})->middleware('api.key');
*/

Route::middleware('api.key')->group(function() {
    //Add prefix
    Route::prefix('v1/user')->group(function() {

        Route::post('/signin', [User::class, 'register']);

        Route::post('/login', [User::class, 'login']);

        //Get user by id
        Route::get('/{id}', [User::class, 'getUserById'])->middleware('jwt.auth');

    });
});
