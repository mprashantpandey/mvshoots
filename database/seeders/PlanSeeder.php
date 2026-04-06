<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $wedding = Category::where('name', 'Wedding')->first();
        $maternity = Category::where('name', 'Maternity')->first();
        $birthday = Category::where('name', 'Birthday')->first();

        $plans = [
            [$wedding?->id, 'Wedding Classic', 25000, '6 hours', ['1 photographer', '200 edited photos', 'Highlight reel']],
            [$wedding?->id, 'Wedding Premium', 50000, '12 hours', ['2 photographers', 'Cinematic video', '500 edited photos']],
            [$maternity?->id, 'Maternity Studio', 12000, '2 hours', ['Studio shoot', '3 costume changes', '25 edits']],
            [$birthday?->id, 'Birthday Celebration', 10000, '3 hours', ['Event coverage', '50 edits', '1 teaser reel']],
        ];

        foreach ($plans as [$categoryId, $title, $price, $duration, $inclusions]) {
            if (! $categoryId) {
                continue;
            }

            Plan::updateOrCreate(
                ['title' => $title],
                [
                    'category_id' => $categoryId,
                    'description' => $title . ' package',
                    'price' => $price,
                    'duration' => $duration,
                    'inclusions' => $inclusions,
                    'status' => 'active',
                ]
            );
        }
    }
}
