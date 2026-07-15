<?php

namespace Database\Seeders;

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
           $admin = User::firstOrCreate(
            ['email' => 'admin@barber.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );

        $admin->assignRole('admin');

        $employee = User::firstOrCreate(
            ['email' => 'employee@barber.com'], 
            [
                'name' => 'Employee',
                'password' => Hash::make('12345678'),
            ]
        );

        $employee->assignRole('barber');
    
    }
}
