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

// 3. Route Admin Dashboard (Dilindungi middleware auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('layouts.admin');
    });
});