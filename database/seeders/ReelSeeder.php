<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reel;
use Illuminate\Database\Seeder;

class ReelSeeder extends Seeder
{
    public function run(): void
    {
        $wedding = Category::where('name', 'Wedding')->first();
        $birthday = Category::where('name', 'Birthday')->first();

        $reels = [
            ['title' => 'Grand Wedding Highlights', 'video_url' => 'https://example.com/reels/wedding-highlights.mp4', 'thumbnail' => 'https://placehold.co/400x700?text=Wedding+Reel', 'category_id' => $wedding?->id],
            ['title' => 'Birthday Moments Reel', 'video_url' => 'https://example.com/reels/birthday-moments.mp4', 'thumbnail' => 'https://placehold.co/400x700?text=Birthday+Reel', 'category_id' => $birthday?->id],
        ];

        foreach ($reels as $reel) {
            Reel::updateOrCreate(['title' => $reel['title']], [...$reel, 'status' => 'active']);
        }
    }
}
