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