<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_admin_cannot_open_platform_only_routes(): void
    {
        $city = City::create([
            'name' => 'Indore',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $admin = Admin::create([
            'name' => 'City Admin',
            'email' => 'city.admin@test',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.cities.index'))->assertForbidden();
        $this->get(route('admin.categories.index'))->assertForbidden();
        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.notifications.index'))->assertForbidden();
    }

    public function test_city_admin_can_open_dashboard_and_bookings(): void
    {
        $city = City::create([
            'name' => 'Pune',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $admin = Admin::create([
            'name' => 'City Admin',
            'email' => 'city.ops@test',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.bookings.index'))->assertOk();
        $this->get(route('admin.partners.index'))->assertOk();
        $this->get(route('admin.reports.index'))->assertOk();
    }
}
