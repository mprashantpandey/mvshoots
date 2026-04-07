<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_only_active_cities(): void
    {
        City::create(['name' => 'Mumbai', 'status' => 'active', 'sort_order' => 1]);
        City::create(['name' => 'Delhi', 'status' => 'inactive', 'sort_order' => 2]);

        $this->getJson('/api/v1/cities')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Mumbai');
    }

    public function test_categories_and_plans_are_filtered_by_city(): void
    {
        $mumbai = City::create(['name' => 'Mumbai', 'status' => 'active', 'sort_order' => 1]);
        $delhi = City::create(['name' => 'Delhi', 'status' => 'active', 'sort_order' => 2]);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding shoots',
            'status' => 'active',
        ]);

        $planMumbai = Plan::create([
            'category_id' => $category->id,
            'title' => 'Mumbai Premium',
            'description' => 'Mumbai plan',
            'price' => 10000,
            'duration' => '4 hours',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);
        $planMumbai->cities()->sync([$mumbai->id]);

        $planDelhi = Plan::create([
            'category_id' => $category->id,
            'title' => 'Delhi Premium',
            'description' => 'Delhi plan',
            'price' => 12000,
            'duration' => '5 hours',
            'inclusions' => ['Photos', 'Video'],
            'status' => 'active',
        ]);
        $planDelhi->cities()->sync([$delhi->id]);

        $this->getJson("/api/v1/categories?city_id={$mumbai->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/v1/categories/{$category->id}/plans?city_id={$mumbai->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Mumbai Premium');
    }

    public function test_user_cannot_create_booking_for_plan_outside_selected_city(): void
    {
        $mumbai = City::create(['name' => 'Mumbai', 'status' => 'active', 'sort_order' => 1]);
        $delhi = City::create(['name' => 'Delhi', 'status' => 'active', 'sort_order' => 2]);

        $user = User::create([
            'name' => 'City User',
            'phone' => '+919999999999',
            'city' => $mumbai->name,
            'city_id' => $mumbai->id,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Portrait',
            'description' => 'Portrait shoots',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Delhi Only Portrait',
            'description' => 'Only available in Delhi',
            'price' => 5000,
            'duration' => '2 hours',
            'inclusions' => ['10 edits'],
            'status' => 'active',
        ]);
        $plan->cities()->sync([$delhi->id]);

        $token = $user->createToken('city-user')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addDays(5)->toDateString(),
                'booking_time' => '10:30',
                'address' => 'Test address',
            ])
            ->assertStatus(422);
    }
}
