<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/* -------------------------------
| Public
| ------------------------------- */
Route::get('/home', function () {
    return view('welcome');
});

Route::get('/', HomeController::class)->name('home');

/* -------------------------------
| Customer Authentication
| ------------------------------- */
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout-user', [AuthController::class, 'logout'])->middleware('auth:customer')->name('logout');

/* -------------------------------
| Customer Routes (authenticated customer-only)
| ------------------------------- */
Route::middleware('auth:customer')->group(function () {
    // Add customer-only routes here (bookings, profile, etc.)
    // Example placeholder (keep existing naming convention):
    // Route::get('/bookings', CustomerBookingController::class)->name('bookings.index');
});

/* -------------------------------
| Employee Authentication
| ------------------------------- */
 

   Route::get('/employee/login', function () {return view('admin.login'); })->name('employee.login');
    Route::post('/employee/login', [AuthAdminController::class, 'login'])->name('employee.login.submit');
 

Route::post('/employee/logout-emp', [AuthAdminController::class, 'logout'])->middleware('auth:employee')->name('employee.logout');

/* -------------------------------
| Admin Routes (auth:employee + role:admin)
| Prefix: /admin
| Route names start with admin.
| ------------------------------- */
Route::prefix('admin')->middleware(['auth:employee', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // Employees management
    Route::livewire('/employees', 'pages::employees.index')->name('employees.index');
    Route::livewire('/employees/create', 'pages::employees.create')->name('employees.create');
    Route::livewire('/employees/{employee}/edit', 'pages::employees.edit')->name('employees.edit');
    Route::livewire('/employees/{employee}/show', 'pages::employees.show')->name('employees.show');

    // Users management
    Route::livewire('/users', 'pages::users.index')->name('users.index');
    Route::livewire('/users/create', 'pages::users.create')->name('users.create');
    Route::livewire('/users/{user}/edit', 'pages::users.edit')->name('users.edit');

    // Bookings management
    Route::livewire('/bookings', 'pages::bookings.index')->name('bookings.index');
    Route::livewire('/bookings/create', 'pages::bookings.create')->name('bookings.create');
    Route::livewire('/bookings/{booking}/edit', 'pages::bookings.edit')->name('bookings.edit');

    // Services management
    Route::livewire('/services', 'pages::services.index')->name('services.index');
    Route::livewire('/services/create', 'pages::services.create')->name('services.create');
    Route::livewire('/services/{service}/edit', 'pages::services.edit')->name('services.edit');
});

/* -------------------------------
| Employee / Barber Routes
| Prefix: /employee
| Middleware: auth:employee + role:employee,barber
| Route names start with employee.
| ------------------------------- */
Route::prefix('employee')->middleware(['auth:employee', 'role:employee|barber'])->name('employee.')->group(function () {
    Route::get('/dashboard', fn () => view('admin'))->name('dashboard');

    Route::livewire('/bookings', 'pages::bookings.index')->name('bookings.index');
    Route::livewire('/bookings/create', 'pages::bookings.create')->name('bookings.create');
    Route::livewire('/bookings/{booking}/edit', 'pages::bookings.edit')->name('bookings.edit');

    Route::get('/profile/{employee}', fn ($employee) => view('admin.profile', compact('employee')))->name('profile.show');
});
