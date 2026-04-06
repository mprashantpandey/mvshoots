<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_an_owner(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $createResponse = $this->post(route('admin.owners.store'), [
            'name' => 'Business Owner',
            'email' => 'owner2@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $createResponse->assertRedirect(route('admin.owners.index'));

        $owner = Owner::where('email', 'owner2@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $owner->password));

        $updateResponse = $this->put(route('admin.owners.update', $owner), [
            'name' => 'Business Owner Updated',
            'email' => 'owner2@example.com',
            'password' => '',
            'status' => 'inactive',
        ]);

        $updateResponse->assertRedirect(route('admin.owners.index'));

        $owner->refresh();

        $this->assertSame('Business Owner Updated', $owner->name);
        $this->assertSame('inactive', $owner->status);

        $deleteResponse = $this->delete(route('admin.owners.destroy', $owner));

        $deleteResponse->assertRedirect(route('admin.owners.index'));
        $this->assertDatabaseMissing('owners', [
            'id' => $owner->id,
        ]);
    }
}
