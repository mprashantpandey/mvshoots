<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            ['name' => 'Asha Films', 'phone' => '9111111111', 'email' => 'asha@partners.test', 'status' => 'active'],
            ['name' => 'Lens Crew', 'phone' => '9222222222', 'email' => 'lens@partners.test', 'status' => 'active'],
            ['name' => 'Moments Studio', 'phone' => '9333333333', 'email' => 'moments@partners.test', 'status' => 'inactive'],
        ];

        foreach ($partners as $partner) {
            Partner::updateOrCreate(['phone' => $partner['phone']], $partner);
        }
    }
}
