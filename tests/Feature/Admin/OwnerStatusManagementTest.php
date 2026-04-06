<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_owner_status(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $owner = Owner::create([
            'name' => 'Toggle Owner',
            'email' => 'toggle-owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin');

        $this->post(route('admin.owners.update-status', $owner), [
            'status' => 'inactive',
        ])->assertRedirect();

        $owner->refresh();

        $this->assertSame('inactive', $owner->status);
    }
}
