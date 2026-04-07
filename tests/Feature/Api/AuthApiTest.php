<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use App\Models\DeviceToken;
use App\Models\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sync_returns_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/user/sync', [
            'name' => 'Test User',
            'city' => 'Hyderabad',
            'phone' => '+911234567890',
            'email' => 'user@example.com',
            'firebase_uid' => 'firebase-user-1',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'phone', 'email', 'city', 'firebase_uid'],
                ],
            ]);
    }

    public function test_user_sync_requires_registration_when_phone_is_new_and_profile_is_incomplete(): void
    {
        $response = $this->postJson('/api/v1/auth/user/sync', [
            'phone' => '+919123456789',
            'firebase_uid' => 'firebase-user-2',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.requires_registration', true)
            ->assertJsonPath('data.is_new', true)
            ->assertJsonPath('data.token', null)
            ->assertJsonPath('data.user', null);
    }

    public function test_partner_sync_returns_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/partner/sync', [
            'name' => 'Test Partner',
            'phone' => '+919876543210',
            'email' => 'partner@example.com',
            'firebase_uid' => 'firebase-partner-1',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'partner' => ['id', 'name', 'phone', 'email', 'firebase_uid', 'status'],
                ],
            ]);
    }

    public function test_partner_sync_requires_registration_when_phone_is_new_and_profile_is_incomplete(): void
    {
        $response = $this->postJson('/api/v1/auth/partner/sync', [
            'phone' => '+919876543211',
            'firebase_uid' => 'firebase-partner-2',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.requires_registration', true)
            ->assertJsonPath('data.is_new', true)
            ->assertJsonPath('data.token', null)
            ->assertJsonPath('data.partner', null);
    }

    public function test_authenticated_actor_can_fetch_and_update_profiles(): void
    {
        $user = User::create([
            'name' => 'Profile User',
            'phone' => '+919999999999',
            'email' => 'profile.user@example.com',
            'city' => 'Bengaluru',
            'firebase_uid' => 'firebase-profile-user',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Profile Partner',
            'phone' => '+918888888888',
            'email' => 'profile.partner@example.com',
            'firebase_uid' => 'firebase-profile-partner',
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Profile Owner',
            'email' => 'profile.owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'name' => 'Profile Admin',
            'email' => 'profile.admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->withToken($user->createToken('user-profile')->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.actor_type', 'user')
            ->assertJsonPath('data.profile.id', $user->id);

        $this->app['auth']->forgetGuards();
        $this->withToken($user->createToken('user-profile-update')->plainTextToken)
            ->putJson('/api/v1/auth/user/profile', [
                'name' => 'Updated User',
                'email' => 'updated.user@example.com',
                'city' => 'Mumbai',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated User')
            ->assertJsonPath('data.city', 'Mumbai');

        $this->app['auth']->forgetGuards();
        $this->withToken($partner->createToken('partner-profile')->plainTextToken)
            ->getJson('/api/v1/auth/partner/me')
            ->assertOk()
            ->assertJsonPath('data.id', $partner->id);

        $this->app['auth']->forgetGuards();
        $this->withToken($owner->createToken('owner-profile')->plainTextToken)
            ->putJson('/api/v1/auth/owner/profile', [
                'name' => 'Updated Owner',
                'email' => 'updated.owner@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Owner');

        $this->app['auth']->forgetGuards();
        $this->withToken($admin->createToken('admin-profile')->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.actor_type', 'admin');
    }

    public function test_admin_can_login_through_owner_app_endpoint(): void
    {
        Admin::create([
            'name' => 'Mobile Admin',
            'email' => 'mobile.admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/v1/auth/owner/login', [
            'email' => 'mobile.admin@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('data.actor_type', 'admin')
            ->assertJsonPath('data.owner.email', 'mobile.admin@example.com');
    }

    public function test_authenticated_user_can_delete_account(): void
    {
        $user = User::create([
            'name' => 'Delete Me',
            'phone' => '+911111111111',
            'email' => 'delete@example.com',
            'city' => 'Pune',
            'firebase_uid' => 'firebase-delete-user',
            'status' => 'active',
        ]);

        DeviceToken::create([
            'user_type' => 'user',
            'user_id' => $user->id,
            'device_token' => 'token-123',
            'platform' => 'android',
        ]);

        AppNotification::create([
            'user_type' => 'user',
            'user_id' => $user->id,
            'title' => 'Hello',
            'body' => 'Body',
            'type' => 'booking_created',
            'is_read' => false,
        ]);

        $this->withToken($user->createToken('delete-user')->plainTextToken)
            ->deleteJson('/api/v1/auth/user/account')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('device_tokens', ['user_id' => $user->id, 'user_type' => 'user']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'user_type' => 'user']);
    }

    public function test_authenticated_partner_can_delete_account(): void
    {
        $partner = Partner::create([
            'name' => 'Delete Partner',
            'phone' => '+919999000000',
            'email' => 'partner.delete@example.com',
            'firebase_uid' => 'firebase-delete-partner',
            'status' => 'active',
        ]);

        DeviceToken::create([
            'user_type' => 'partner',
            'user_id' => $partner->id,
            'device_token' => 'partner-token-123',
            'platform' => 'android',
        ]);

        AppNotification::create([
            'user_type' => 'partner',
            'user_id' => $partner->id,
            'title' => 'Partner Hello',
            'body' => 'Body',
            'type' => 'booking_assigned',
            'is_read' => false,
        ]);

        $this->withToken($partner->createToken('delete-partner')->plainTextToken)
            ->deleteJson('/api/v1/auth/partner/account')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
        $this->assertDatabaseMissing('device_tokens', ['user_id' => $partner->id, 'user_type' => 'partner']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $partner->id, 'user_type' => 'partner']);
    }

    public function test_authenticated_admin_can_change_password_and_delete_account(): void
    {
        $admin = Admin::create([
            'name' => 'Delete Admin',
            'email' => 'delete.admin@example.com',
            'password' => bcrypt('password'),
        ]);

        DeviceToken::create([
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'device_token' => 'admin-token-123',
            'platform' => 'android',
        ]);

        AppNotification::create([
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'title' => 'Admin Hello',
            'body' => 'Body',
            'type' => 'system',
            'is_read' => false,
        ]);

        $token = $admin->createToken('admin-account')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/auth/owner/password', [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword', $admin->fresh()->password));

        $this->app['auth']->forgetGuards();
        $this->withToken($admin->createToken('admin-delete')->plainTextToken)
            ->deleteJson('/api/v1/auth/owner/account')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
        $this->assertDatabaseMissing('device_tokens', ['user_id' => $admin->id, 'user_type' => 'admin']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $admin->id, 'user_type' => 'admin']);
    }
}
