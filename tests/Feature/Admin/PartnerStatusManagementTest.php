<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_partner_status(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $partner = Partner::create([
            'name' => 'Toggle Partner',
            'phone' => '9012222222',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin');

        $this->post(route('admin.partners.update-status', $partner), [
            'status' => 'inactive',
        ])->assertRedirect();

        $partner->refresh();

        $this->assertSame('inactive', $partner->status);
    }
}
