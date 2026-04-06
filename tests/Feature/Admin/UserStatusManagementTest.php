<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Status User',
            'phone' => '9011111111',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin');

        $this->post(route('admin.users.update-status', $user), [
            'status' => 'inactive',
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('inactive', $user->status);
    }
}
