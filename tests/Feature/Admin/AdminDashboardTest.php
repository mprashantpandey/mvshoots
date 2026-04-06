<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_is_accessible(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_admin_can_login_and_view_dashboard(): void
    {
        Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@vmshoot.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_guest_is_redirected_from_dashboard_to_admin_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }
}
