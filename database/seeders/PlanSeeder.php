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
        $preWedding = Category::where('name', 'Pre Wedding')->first();
        $babyShower = Category::where('name', 'Baby Shower')->first();
        $family = Category::where('name', 'Family Portraits')->first();
        $corporate = Category::where('name', 'Corporate')->first();

        $plans = [
            [$wedding?->id, 'Wedding Classic', 25000, '6 hours', ['1 photographer', '200 edited photos', 'Highlight reel']],
            [$wedding?->id, 'Wedding Premium', 50000, '12 hours', ['2 photographers', 'Cinematic video', '500 edited photos']],
            [$wedding?->id, 'Wedding Luxe', 78000, 'Full day', ['2 photographers', '1 cinematographer', 'Drone coverage', 'Wedding teaser', '600 edited photos']],
            [$maternity?->id, 'Maternity Studio', 12000, '2 hours', ['Studio shoot', '3 costume changes', '25 edits']],
            [$maternity?->id, 'Maternity Signature', 18000, '3 hours', ['Indoor + outdoor shoot', 'Makeup support', '40 edits', '1 teaser reel']],
            [$birthday?->id, 'Birthday Celebration', 10000, '3 hours', ['Event coverage', '50 edits', '1 teaser reel']],
            [$birthday?->id, 'Birthday Premium', 16500, '5 hours', ['Photographer + candid coverage', '100 edits', 'Highlight video']],
            [$preWedding?->id, 'Pre Wedding Classic', 22000, '5 hours', ['1 location', '50 edits', 'Cinematic teaser']],
            [$preWedding?->id, 'Pre Wedding Destination', 42000, 'Full day', ['2 locations', 'Drone shots', '120 edits', 'Short film']],
            [$babyShower?->id, 'Baby Shower Moments', 9500, '3 hours', ['Decor shots', 'Family portraits', '50 edits']],
            [$family?->id, 'Family Portrait Session', 8500, '90 mins', ['1 location', '20 edits', 'Group portraits']],
            [$family?->id, 'Family Story Session', 14500, '3 hours', ['Lifestyle storytelling', '50 edits', '1 highlight reel']],
            [$corporate?->id, 'Corporate Team Shoot', 18000, '4 hours', ['Team portraits', 'Office candid shots', '40 edits']],
            [$corporate?->id, 'Corporate Event Coverage', 30000, '8 hours', ['Stage coverage', 'Guest candid shots', '80 edits', 'Event reel']],
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
