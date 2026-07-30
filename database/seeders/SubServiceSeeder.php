<?php

namespace Database\Seeders;

use App\Models\SubService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         SubService::factory(6)->create();
    }
}
