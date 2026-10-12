<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ABCController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('v1')->group(function () {
    // 1. Guest Routes (Tanpa Auth)
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // 2. Authenticated Routes - Semua Role yang Login (Admin & Customer)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // 3. Khusus Hak Akses Admin Saja
        Route::middleware('admin')->group(function () {
            Route::get('/products', [ProductController::class, 'index']);
            Route::get('/abc/generate-bundle', [ABCController::class, 'generateBundle']);
        });

        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions', [TransactionController::class, 'index']);
    });
});
