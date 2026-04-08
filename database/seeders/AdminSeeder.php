<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@mvshoots.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'city_id' => null,
                'is_main' => true,
            ]
        );
    }
}
