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
            Route::get('/reports/sales', [ReportController::class, 'index']);
        });

        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions', [TransactionController::class, 'index']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);

        Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index']);
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store']);

        Route::apiResource('customers', CustomerController::class);
        Route::patch('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus']);

        Route::get('/reports/sales', [ReportController::class, 'index']);
        Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportPdf']);
        Route::get('/reports/sales/export-excel', [ReportController::class, 'exportExcel']);
    });
});
