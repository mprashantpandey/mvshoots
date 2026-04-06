<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_partner(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.partners.store'), [
            'name' => 'Lens Crew',
            'phone' => '8888882222',
            'email' => 'partner@example.com',
            'status' => 'active',
        ]);

        $createResponse->assertRedirect(route('admin.partners.index'));

        $partner = Partner::firstOrFail();

        $this->assertSame('Lens Crew', $partner->name);

        $updateResponse = $this->put(route('admin.partners.update', $partner), [
            'name' => 'Lens Crew Plus',
            'phone' => '8888882222',
            'email' => 'partner-plus@example.com',
            'status' => 'inactive',
        ]);

        $updateResponse->assertRedirect(route('admin.partners.index'));

        $partner->refresh();

        $this->assertSame('Lens Crew Plus', $partner->name);
        $this->assertSame('inactive', $partner->status);

        $deleteResponse = $this->delete(route('admin.partners.destroy', $partner));

        $deleteResponse->assertRedirect(route('admin.partners.index'));
        $this->assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);
    }
}
