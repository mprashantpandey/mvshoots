<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_can_view_staff_and_create_city_admin(): void
    {
        $city = City::create([
            'name' => 'Test City',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $main = Admin::create([
            'name' => 'Main',
            'email' => 'main@staff.test',
            'password' => bcrypt('password'),
            'city_id' => null,
            'is_main' => true,
        ]);

        $this->actingAs($main, 'admin');

        $this->get(route('admin.staff.index'))->assertOk();

        $this->post(route('admin.staff.store'), [
            'name' => 'City Ops',
            'email' => 'city.ops@staff.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city_id' => $city->id,
        ])->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('admins', [
            'email' => 'city.ops@staff.test',
            'city_id' => $city->id,
            'is_main' => false,
        ]);
    }

    public function test_city_admin_cannot_access_staff_pages(): void
    {
        $city = City::create([
            'name' => 'X',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $cityAdmin = Admin::create([
            'name' => 'City',
            'email' => 'city@staff.test',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
            'is_main' => false,
        ]);

        $this->actingAs($cityAdmin, 'admin');

        $this->get(route('admin.staff.index'))->assertForbidden();
    }
}
