<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_can_fetch_earnings_dashboard(): void
    {
        $partner = Partner::create([
            'name' => 'Partner Dash',
            'phone' => '+919999999901',
            'email' => 'partner.dash@example.com',
            'firebase_uid' => 'firebase-partner-dash',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'U',
            'phone' => '+919999999902',
            'email' => 'u@example.com',
            'firebase_uid' => 'firebase-u',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Cat',
            'description' => 'D',
            'image' => 'c.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Plan',
            'description' => 'P',
            'price' => 10000,
            'duration' => '1h',
            'inclusions' => ['x'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'address' => 'A',
            'status' => 'completed',
            'total_amount' => 10000,
            'advance_amount' => 2000,
            'final_amount' => 8000,
            'advance_paid' => true,
            'final_paid' => true,
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'payment_type' => 'final',
            'amount' => 8000,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $token = $partner->createToken('partner-dash')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/partner/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.final_earnings_received', 8000)
            ->assertJsonPath('data.stats.assigned_bookings_count', 1)
            ->assertJsonPath('data.stats.completed_bookings_count', 1);
    }

    public function test_non_partner_cannot_fetch_partner_dashboard(): void
    {
        $user = User::create([
            'name' => 'Customer',
            'phone' => '+919999999903',
            'email' => 'c@example.com',
            'firebase_uid' => 'firebase-c',
            'status' => 'active',
        ]);

        $token = $user->createToken('user')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/partner/dashboard')
            ->assertForbidden();
    }
}
