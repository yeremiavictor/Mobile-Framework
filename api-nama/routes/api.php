<?php

// auth
use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//posts
// Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class );

//register
Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');

//login
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//post login req
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Update agar CRUD hanya bisa setelah login
Route::middleware('auth:api')->group(function () {
    // Post CRUD (hanya untuk user yang sudah login)
    Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);

    // Get user info
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});


//logout
Route::post('/logout', App\Http\Controllers\Api\LogoutController::class)->name('logout');
