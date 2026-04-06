<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Aarav Kumar', 'phone' => '9000000001', 'email' => 'aarav@example.com', 'status' => 'active'],
            ['name' => 'Isha Reddy', 'phone' => '9000000002', 'email' => 'isha@example.com', 'status' => 'active'],
            ['name' => 'Riya Sharma', 'phone' => '9000000003', 'email' => 'riya@example.com', 'status' => 'inactive'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['phone' => $user['phone']], $user);
        }
    }
}
