<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $sliders = [
            [
                'title' => 'Capture your best moments',
                'subtitle' => 'Book premium shoots in minutes.',
                'image' => 'https://dummyimage.com/1200x720/e9eef8/1f3a5b.png&text=MV+Shoots+User+1',
                'app_target' => 'user',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Trusted creatives for every event',
                'subtitle' => 'Wedding, maternity, birthday and more.',
                'image' => 'https://dummyimage.com/1200x720/f6efe7/7f5432.png&text=MV+Shoots+User+2',
                'app_target' => 'both',
                'sort_order' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::updateOrCreate(
                ['title' => $slider['title'], 'app_target' => $slider['app_target']],
                $slider
            );
        }
    }
}
