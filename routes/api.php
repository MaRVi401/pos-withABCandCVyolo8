<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ABCController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\StockAdjustmentController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ChatbotController;

Route::prefix('v1')->group(function () {
    // 1. Guest Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // 2. Authenticated Routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // 3. Admin Restricted Routes
        Route::middleware('admin')->group(function () {
            // Master Data
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('products', ProductController::class);
            Route::apiResource('customers', CustomerController::class);
            Route::patch('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus']);

            // POS & Stok
            Route::get('/transactions', [TransactionController::class, 'index']);
            Route::post('/transactions', [TransactionController::class, 'store']);
            Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index']);
            Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store']);

            // Optimasi ABC & Chatbot AI
            Route::get('/abc/generate-bundle', [ABCController::class, 'generateBundle']);
            Route::post('/chatbot/chat', [ChatbotController::class, 'chat']);

            // Reports
            Route::get('/reports/sales', [ReportController::class, 'index']);
            Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportPdf']);
            Route::get('/reports/sales/export-excel', [ReportController::class, 'exportExcel']);
        });
    });
});