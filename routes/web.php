<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return view('welcome');
});


Route::get('/', HomeController::class)->name('home');


Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('web.auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', fn () => view('web.auth.signup'))->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');

                            /*======= Start Employees ========*/
Route::livewire('/employees', 'pages::employees.index')->name('employees.index');
Route::livewire('/employees/create', 'pages::employees.create')->name('employees.create');
Route::livewire('/employees/{employee}/edit', 'pages::employees.edit')->name('employees.edit');
                            /*======= Start Users ========*/
Route::livewire('/users', 'pages::users.index')->name('users.index');
Route::livewire('/users/create', 'pages::users.create')->name('users.create');
Route::livewire('/users/{user}/edit', 'pages::users.edit')->name('users.edit');
   /*======= End Users ========*/


         /*======= Start Booking ========*/
Route::livewire('/bookings', 'pages::bookings.index')->name('bookings.index');
Route::livewire('/bookings/create', 'pages::bookings.create')->name('bookings.create');
Route::livewire('/bookings/{booking}/edit', 'pages::bookings.edit')->name('bookings.edit');
      /*======= End Booking ========*/
         /*======= Start Services ========*/
Route::livewire('/services', 'pages::services.index')->name('services.index');
Route::livewire('/services/create', 'pages::services.create')->name('services.create');
Route::livewire('/services/{service}/edit', 'pages::services.edit')->name('services.edit');
      /*======= End Services ========*/
    });
// Route::middleware(['auth', 'role:barber'])->group(function () {
    Route::get('/admin/barber', fn () => view('admin.barber'))->name('admin.barber');

// });