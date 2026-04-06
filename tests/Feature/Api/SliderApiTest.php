<?php

namespace Tests\Feature\Api;

use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SliderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sliders_endpoint_returns_only_active_items_for_requested_app(): void
    {
        Slider::create([
            'title' => 'User only',
            'image' => 'sliders/user.jpg',
            'app_target' => 'user',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        Slider::create([
            'title' => 'Both apps',
            'image' => 'sliders/both.jpg',
            'app_target' => 'both',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        Slider::create([
            'title' => 'Partner only',
            'image' => 'sliders/partner.jpg',
            'app_target' => 'partner',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        Slider::create([
            'title' => 'Inactive',
            'image' => 'sliders/inactive.jpg',
            'app_target' => 'user',
            'sort_order' => 0,
            'status' => 'inactive',
        ]);

        $response = $this->getJson('/api/v1/sliders?app=user');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Both apps')
            ->assertJsonPath('data.1.title', 'User only');
    }

    public function test_sliders_endpoint_requires_valid_app_parameter(): void
    {
        $this->getJson('/api/v1/sliders')
            ->assertStatus(422);

        $this->getJson('/api/v1/sliders?app=owner')
            ->assertStatus(422);
    }
}
