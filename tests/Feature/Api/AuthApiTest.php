<?php

namespace Tests\Feature\Api;

use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sync_returns_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/user/sync', [
            'name' => 'Test User',
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
                    'user' => ['id', 'name', 'phone', 'email', 'firebase_uid'],
                ],
            ]);
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

    public function test_authenticated_actor_can_fetch_and_update_profiles(): void
    {
        $user = User::create([
            'name' => 'Profile User',
            'phone' => '+919999999999',
            'email' => 'profile.user@example.com',
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
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated User');

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
    }
}
