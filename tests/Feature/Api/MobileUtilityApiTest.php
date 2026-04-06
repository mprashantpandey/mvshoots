<?php

namespace Tests\Feature\Api;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileUtilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_fetch_dashboard_and_partner_list(): void
    {
        $owner = Owner::create([
            'name' => 'Owner',
            'email' => 'owner@vmshoot.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'User One',
            'phone' => '+911234567000',
            'email' => 'user.one@example.com',
            'firebase_uid' => 'firebase-user-one',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Partner One',
            'phone' => '+911234567001',
            'email' => 'partner.one@example.com',
            'firebase_uid' => 'firebase-partner-one',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding shoots',
            'image' => 'categories/wedding.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Premium',
            'description' => 'Premium package',
            'price' => 10000,
            'duration' => '6 hours',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->addDays(5)->toDateString(),
            'booking_time' => '10:00',
            'address' => 'Owner dashboard street',
            'status' => 'assigned',
            'total_amount' => 10000,
            'advance_amount' => 2000,
            'final_amount' => 8000,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        $token = $owner->createToken('owner-dashboard')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/owner/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.total_users', 1)
            ->assertJsonPath('data.stats.total_partners', 1)
            ->assertJsonPath('data.stats.total_bookings', 1);

        $this->withToken($token)
            ->getJson('/api/v1/partners')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $partner->id)
            ->assertJsonPath('data.data.0.name', 'Partner One');
    }

    public function test_user_can_create_payment_intents_and_mark_notifications_read(): void
    {
        $user = User::create([
            'name' => 'Utility User',
            'phone' => '+911234567100',
            'email' => 'utility.user@example.com',
            'firebase_uid' => 'firebase-utility-user',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Portrait',
            'description' => 'Portrait shoots',
            'image' => 'categories/portrait.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Portrait Gold',
            'description' => 'Portrait package',
            'price' => 5000,
            'duration' => '2 hours',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'booking_time' => '11:00',
            'address' => 'Intent street',
            'status' => 'pending',
            'total_amount' => 5000,
            'advance_amount' => 1000,
            'final_amount' => 4000,
            'advance_paid' => false,
            'final_paid' => false,
        ]);

        $notification = AppNotification::create([
            'user_type' => 'user',
            'user_id' => $user->id,
            'title' => 'Test notification',
            'body' => 'Read me',
            'type' => 'custom',
            'reference_id' => $booking->id,
            'is_read' => false,
        ]);

        $token = $user->createToken('user-utility')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/payments/advance-intent', [
                'booking_id' => $booking->id,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['order_id', 'amount', 'currency', 'payment_type', 'booking_id'],
            ]);

        $this->withToken($token)
            ->postJson("/api/v1/notifications/{$notification->id}/mark-read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->withToken($token)
            ->postJson('/api/v1/notifications/mark-all-read')
            ->assertOk();
    }
}
