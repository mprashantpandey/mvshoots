<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Wedding', 'description' => 'Wedding photography and videography packages', 'image' => 'https://dummyimage.com/600x400/e9eef8/1f3a5b.png&text=Wedding'],
            ['name' => 'Maternity', 'description' => 'Studio and outdoor maternity shoots', 'image' => 'https://dummyimage.com/600x400/f7efe8/8b5e3c.png&text=Maternity'],
            ['name' => 'Birthday', 'description' => 'Birthday party event coverage and candid shoots', 'image' => 'https://dummyimage.com/600x400/fff1e8/c96b36.png&text=Birthday'],
            ['name' => 'Pre Wedding', 'description' => 'Destination, concept, and cinematic pre wedding shoots', 'image' => 'https://dummyimage.com/600x400/f4eafd/6f4db8.png&text=Pre+Wedding'],
            ['name' => 'Baby Shower', 'description' => 'Elegant home and venue coverage for baby shower events', 'image' => 'https://dummyimage.com/600x400/eef7f1/2f7a4a.png&text=Baby+Shower'],
            ['name' => 'Family Portraits', 'description' => 'Indoor and outdoor family photography packages', 'image' => 'https://dummyimage.com/600x400/f8f4ea/8a6c2f.png&text=Family'],
            ['name' => 'Corporate', 'description' => 'Professional corporate shoots, team portraits, and events', 'image' => 'https://dummyimage.com/600x400/e9eef8/1f3a5b.png&text=Corporate'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], [...$category, 'status' => 'active']);
        }
    }
}
