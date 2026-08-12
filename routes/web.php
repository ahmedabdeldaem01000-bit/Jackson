<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/home', function () {
    return view('welcome');
});

Route::get('/', HomeController::class)->name('home');


/*
|--------------------------------------------------------------------------
| Customer Authentication
|--------------------------------------------------------------------------
|
| Customer = users table
| Guard = customer
|
*/

Route::middleware('guest:customer')->group(function () {

    Route::get('/login', fn () => view('web.auth.login'))
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');

    Route::get('/register', fn () => view('web.auth.signup'))
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.submit');
});


/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:customer')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Customer Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Customer Profile
    |--------------------------------------------------------------------------
    |
    | نحط هنا صفحات العميل الخاصة به.
    |
    */

    // Route::livewire('/profile', 'pages::customer.profile')
    //     ->name('customer.profile');


    /*
    |--------------------------------------------------------------------------
    | Customer Bookings
    |--------------------------------------------------------------------------
    |
    | حجوزات العميل فقط.
    |
    */

    // Route::livewire('/my-bookings', 'pages::customer.bookings')
    //     ->name('customer.bookings');


    /*
    |--------------------------------------------------------------------------
    | Create Customer Booking
    |--------------------------------------------------------------------------
    */

    // Route::livewire('/book', 'pages::customer.bookings.create')
    //     ->name('customer.booking.create');
});


/*
|--------------------------------------------------------------------------
| Employee Authentication
|--------------------------------------------------------------------------
|
| Admin + Employee
| Guard = employee
|
*/

Route::middleware('guest:employee')->group(function () {

    Route::get('/employee/login', fn () => view('admin.login'))
        ->name('employee.login');

    Route::post('/employee/login', [EmployeeAuthController::class, 'login'])
        ->name('employee.login.submit');
});


/*
|--------------------------------------------------------------------------
| Employee / Admin Logout
|--------------------------------------------------------------------------
|
| الاثنين يستخدموا employee guard
|
*/

Route::middleware('auth:employee')->group(function () {

    Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])
        ->name('employee.logout');
});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
|
| Guard  : employee
| Roles  : admin
|
| الأدمن له كل صلاحيات الإدارة.
|
*/

Route::middleware([
    'auth:employee',
    'role:admin',
])->prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/employees',
        'pages::employees.index'
    )->name('employees.index');

    Route::livewire(
        '/employees/create',
        'pages::employees.create'
    )->name('employees.create');

    Route::livewire(
        '/employees/{employee}/edit',
        'pages::employees.edit'
    )->name('employees.edit');

    Route::livewire(
        '/employees/{employee}/show',
        'pages::employees.show'
    )->name('employees.show');


    /*
    |--------------------------------------------------------------------------
    | Users / Customers
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/users',
        'pages::users.index'
    )->name('users.index');

    Route::livewire(
        '/users/create',
        'pages::users.create'
    )->name('users.create');

    Route::livewire(
        '/users/{user}/edit',
        'pages::users.edit'
    )->name('users.edit');


    /*
    |--------------------------------------------------------------------------
    | Bookings
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/bookings',
        'pages::bookings.index'
    )->name('bookings.index');

    Route::livewire(
        '/bookings/create',
        'pages::bookings.create'
    )->name('bookings.create');

    Route::livewire(
        '/bookings/{booking}/edit',
        'pages::bookings.edit'
    )->name('bookings.edit');


    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/services',
        'pages::services.index'
    )->name('services.index');

    Route::livewire(
        '/services/create',
        'pages::services.create'
    )->name('services.create');

    Route::livewire(
        '/services/{service}/edit',
        'pages::services.edit'
    )->name('services.edit');
});


/*
|--------------------------------------------------------------------------
| EMPLOYEE / BARBER
|--------------------------------------------------------------------------
|
| Guard : employee
| Roles : employee, barber
|
| الموظف يشوف الحجوزات ويشتغل عليها
| بدون صلاحيات إدارة الموظفين أو المستخدمين أو الخدمات.
|
*/

Route::middleware([
    'auth:employee',
    'role:employee,barber',
])->prefix('employee')->name('employee.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Employee Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Today's Bookings
    |--------------------------------------------------------------------------
    |
    | Global Queue
    |
    */

    Route::livewire(
        '/bookings',
        'pages::bookings.index'
    )->name('bookings.index');


    /*
    |--------------------------------------------------------------------------
    | Create Booking
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/bookings/create',
        'pages::bookings.create'
    )->name('bookings.create');


    /*
    |--------------------------------------------------------------------------
    | Edit Booking
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/bookings/{booking}/edit',
        'pages::bookings.edit'
    )->name('bookings.edit');


    /*
    |--------------------------------------------------------------------------
    | Employee Profile
    |--------------------------------------------------------------------------
    */

    Route::livewire(
        '/profile/{employee}',
        'pages::employees.show'
    )->name('profile');
});