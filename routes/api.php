<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

        Route::post('/signin', function(){
            return response()->json([
                'message' => 'Create new user'
            ]);
        });

        Route::post('/login', function(){
            return response()->json([
                'message' => 'Create new user'
            ]);
        });

    });
});
