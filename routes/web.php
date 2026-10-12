<?php

use Illuminate\Support\Facades\Route;

// 1. Route Root langsung di-redirect ke halaman login
Route::get('/', function () {
    return view('welcome');
});

// 2. Route Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// 3. Route Admin Dashboard
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

// 4. Route Admin ABC
Route::get('/admin/abc', function () {
    return view('admin.abc');
});

// 5. Route Admin Products
Route::get('/admin/pos', function () {
    return view('admin.pos');
});

// 6. Route Admin Reports
Route::get('/admin/reports', function () {
    return view('admin.reports');
});

Route::get('/admin/categories', function () {
    return view('admin.categories');
});

Route::get('/admin/products', function () {
    return view('admin.products');
});

Route::get('/admin/stock-adjustments', function () {
    return view('admin.stock_adjustments');
});