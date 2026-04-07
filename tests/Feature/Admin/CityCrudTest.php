<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_city(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $this->post(route('admin.cities.store'), [
            'name' => 'Mumbai',
            'status' => 'active',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.cities.index'));

        $city = City::firstOrFail();
        $this->assertSame('Mumbai', $city->name);

        $this->put(route('admin.cities.update', $city), [
            'name' => 'Mumbai City',
            'status' => 'inactive',
            'sort_order' => 2,
        ])->assertRedirect(route('admin.cities.index'));

        $city->refresh();
        $this->assertSame('Mumbai City', $city->name);
        $this->assertSame('inactive', $city->status);

        $this->delete(route('admin.cities.destroy', $city))
            ->assertRedirect(route('admin.cities.index'));

        $this->assertDatabaseMissing('cities', [
            'id' => $city->id,
        ]);
    }
}
