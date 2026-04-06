<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Wedding', 'description' => 'Wedding photography and videography packages', 'image' => 'https://placehold.co/600x400?text=Wedding'],
            ['name' => 'Maternity', 'description' => 'Studio and outdoor maternity shoots', 'image' => 'https://placehold.co/600x400?text=Maternity'],
            ['name' => 'Birthday', 'description' => 'Birthday party event coverage and candid shoots', 'image' => 'https://placehold.co/600x400?text=Birthday'],
            ['name' => 'Pre Wedding', 'description' => 'Destination, concept, and cinematic pre wedding shoots', 'image' => 'https://placehold.co/600x400?text=Pre+Wedding'],
            ['name' => 'Baby Shower', 'description' => 'Elegant home and venue coverage for baby shower events', 'image' => 'https://placehold.co/600x400?text=Baby+Shower'],
            ['name' => 'Family Portraits', 'description' => 'Indoor and outdoor family photography packages', 'image' => 'https://placehold.co/600x400?text=Family'],
            ['name' => 'Corporate', 'description' => 'Professional corporate shoots, team portraits, and events', 'image' => 'https://placehold.co/600x400?text=Corporate'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], [...$category, 'status' => 'active']);
        }
    }
}
