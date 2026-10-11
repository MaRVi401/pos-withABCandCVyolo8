<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ABCController;

Route::prefix('v1')->group(function () {
    // Guest Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    
    // Authenticated Routes (Butuh Bearer Token Sanctum)
    Route::middleware('auth:sanctum', 'admin')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/abc/generate-bundle', [ABCController::class, 'generateBundle']);
    });
});
