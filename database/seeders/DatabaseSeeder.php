<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            OwnerSeeder::class,
            UserSeeder::class,
            PartnerSeeder::class,
            CategorySeeder::class,
            PlanSeeder::class,
            ReelSeeder::class,
            SliderSeeder::class,
            BookingSeeder::class,
            NotificationSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
