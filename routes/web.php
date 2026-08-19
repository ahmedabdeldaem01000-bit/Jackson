<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Customer\MyBooking;
use App\Livewire\Customer\Profile\Show;



/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/home', function () {
    return view('welcome');
});

Route::get('/', HomeController::class)
    ->name('home');


/*
|--------------------------------------------------------------------------
| Customer Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest:customer')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.submit');


    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/verify-email', [AuthController::class, 'showVerificationForm'])
        ->name('verification.notice');

    Route::post('/verify-email', [AuthController::class, 'verifyOtp'])
        ->name('verification.verify');

    Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])
        ->name('verification.resend');
});


Route::post('/logout-user', [AuthController::class, 'logout'])
    ->middleware('auth:customer')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:customer')->group(function () {

    Route::livewire(
        '/profile',
        'customer.profile.show'
    )->name('customer.profile');

    Route::livewire(
        '/profile/information',
        'customer.profile.information'
    )->name('customer.profile.information');

    Route::livewire(
        '/profile/bookings',
        'customer.profile.bookings'
    )->name('customer.profile.bookings');

    Route::livewire(
        '/profile/notifications',
        'customer.profile.notifications'
    )->name('customer.profile.notifications');

    });

/*
|--------------------------------------------------------------------------
| Employee / Admin Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest:employee')->group(function () {

    Route::get('/employee/login', function () {
        return view('admin.login');
    })->name('employee.login');

    Route::post('/employee/login', [AuthAdminController::class, 'login'])
        ->name('employee.login.submit');
});


Route::post('/employee/logout-emp', [AuthAdminController::class, 'logout'])
    ->middleware('auth:employee')
    ->name('employee.logout');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware([
        'auth:employee',
        'role:admin',
    ])
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::livewire('/dashboard', 'pages::dashboard')
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        Route::livewire('/employees', 'pages::employees.index')
            ->name('employees.index');

        Route::livewire('/employees/create', 'pages::employees.create')
            ->name('employees.create');

        Route::livewire('/employees/{employee}/edit', 'pages::employees.edit')
            ->name('employees.edit');

        Route::livewire('/employees/{employee}/show', 'pages::employees.show')
            ->name('employees.show');


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::livewire('/users', 'pages::users.index')
            ->name('users.index');

        Route::livewire('/users/create', 'pages::users.create')
            ->name('users.create');

        Route::livewire('/users/{user}/edit', 'pages::users.edit')
            ->name('users.edit');


        /*
        |--------------------------------------------------------------------------
        | Bookings
        |--------------------------------------------------------------------------
        */

        Route::livewire('/bookings', 'pages::bookings.index')
            ->name('bookings.index');

        Route::livewire('/bookings/create', 'pages::bookings.create')
            ->name('bookings.create');

        Route::livewire('/bookings/{booking}/edit', 'pages::bookings.edit')
            ->name('bookings.edit');


        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        Route::livewire('/services', 'pages::services.index')
            ->name('services.index');

        Route::livewire('/services/create', 'pages::services.create')
            ->name('services.create');

        Route::livewire('/services/{service}/edit', 'pages::services.edit')
            ->name('services.edit');
    });


/*
|--------------------------------------------------------------------------
| Employee / Barber Routes
|--------------------------------------------------------------------------
*/

Route::prefix('employee')
    ->middleware(['auth:employee', 'role:employee|barber'])
    ->name('employee.')
    ->group(function () {

        Route::livewire('/dashboard', 'pages::employees.dashboard')
            ->name('dashboard');

        Route::livewire('/bookings', 'pages::bookings.index')
            ->name('bookings.index');

        Route::livewire('/bookings/create', 'pages::bookings.create')
            ->name('bookings.create');

        Route::livewire('/bookings/{booking}/edit', 'pages::bookings.edit')
            ->name('bookings.edit');

        Route::livewire('/profile', 'pages::employees.profile')
            ->name('profile.show');
    });