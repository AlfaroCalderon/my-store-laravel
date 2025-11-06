<?php

use App\Http\Controllers\ImageController;
use Cloudinary\Asset\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User;
use App\Http\Controllers\ProductsController;
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

        //Refresh token
        Route::post('/refresh-token', [User::class, 'refreshToken']);

        //Validate access token
        Route::post('/validate-token', [User::class, 'validateAccessToken']);

    });

     Route::prefix('v1/products')->middleware('jwt.auth')->group(function() {
        Route::get('/', [ProductsController::class, 'index']);
        Route::post('/', [ProductsController::class,'store'])->middleware('manager');
        Route::post('/upload-image', [ImageController::class,'upload']);
  });

  });


