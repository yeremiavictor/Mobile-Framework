<?php

// auth
use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//posts
Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class );

//register
Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');

//login
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

