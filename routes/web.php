<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return view('welcome');
});


Route::get('/home', HomeController::class)->name('home');


Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('web.auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', fn () => view('web.auth.signup'))->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/admin/barber', fn () => view('admin.barber'))->name('admin.barber');
// });