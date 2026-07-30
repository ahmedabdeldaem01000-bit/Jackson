<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Service::factory()
            ->count(6)
            ->has(SubService::factory()->count(6), 'subServices')
            ->create();
    }
}
