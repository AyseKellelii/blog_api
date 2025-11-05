<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Kullanıcı kayıt ve giriş işlemleri
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Token gerektiren işlemler
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    //category işlemleri
    Route::apiResource('categories', CategoryController::class);
    //post işlemleri
    Route::apiResource('posts', PostController::class);
    //isme göre post filtreleme
    Route::get('/user/{user}/posts', [PostController::class, 'postsByUser']);

});

//tag ve search
Route::middleware('auth:sanctum')->get('/posts', [PostController::class, 'index']);


