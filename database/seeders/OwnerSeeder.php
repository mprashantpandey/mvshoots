<?php

namespace Database\Seeders;

use App\Models\Owner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        Owner::updateOrCreate(
            ['email' => 'owner@vmshoot.test'],
            [
                'name' => 'Platform Owner',
                'password' => Hash::make('password'),
            ]
        );
    }
}
