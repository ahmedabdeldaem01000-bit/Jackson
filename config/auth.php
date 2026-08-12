<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'customer'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [

        /*
        |--------------------------------------------------------------------------
        | Customer Guard
        |--------------------------------------------------------------------------
        */
        'customer' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        /*
        |--------------------------------------------------------------------------
        | Admin / Employee Guard
        |--------------------------------------------------------------------------
        */
        'employee' => [
            'driver' => 'session',
            'provider' => 'employees',
        ],
    ],

    'providers' => [

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Admins / Employees
        |--------------------------------------------------------------------------
        */
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
    ],

    'passwords' => [

        'users' => [
            'provider' => 'users',
            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),
            'expire' => 60,
            'throttle' => 60,
        ],

        'employees' => [
            'provider' => 'employees',
            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env(
        'AUTH_PASSWORD_TIMEOUT',
        10800
    ),
];