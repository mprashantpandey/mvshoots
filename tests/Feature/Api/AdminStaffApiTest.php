<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\City;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_list_staff_via_api(): void
    {
        City::create([
            'name' => 'Test City',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $main = Admin::create([
            'name' => 'Main',
            'email' => 'main@api.staff.test',
            'password' => bcrypt('password'),
            'city_id' => null,
            'is_main' => true,
        ]);

        $token = $main->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/owner/staff')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'admins',
                ],
            ]);
    }

    public function test_city_admin_cannot_access_staff_api(): void
    {
        $city = City::create([
            'name' => 'Scoped',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $cityAdmin = Admin::create([
            'name' => 'City',
            'email' => 'city@api.staff.test',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
            'is_main' => false,
        ]);

        $token = $cityAdmin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/owner/staff')
            ->assertForbidden();
    }

    public function test_platform_owner_cannot_access_staff_api(): void
    {
        $owner = Owner::create([
            'name' => 'Owner',
            'email' => 'owner@api.staff.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/owner/staff')
            ->assertForbidden();
    }
}
