<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    $user = User::factory(50)->create();
     $user->each(function ($user) {
            $user->assignRole('customer');
        });

           $admin = Employee::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => '1234567890',
                'password' => Hash::make('12345678'),
            ]
        );

        $admin->assignRole('admin');

        $employee = Employee::firstOrCreate(
            ['email' => 'employee@gmail.com'], 
            [
                'name' => 'Employee',
                'phone' => '0987654321',
                'password' => Hash::make('12345678'),
            ]
        );

        $employee->assignRole('barber');
    
    }
}
