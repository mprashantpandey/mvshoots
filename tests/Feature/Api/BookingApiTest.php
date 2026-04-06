<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_booking_without_passing_user_id(): void
    {
        $user = User::create([
            'name' => 'Booking User',
            'phone' => '+911111111111',
            'email' => 'booking.user@example.com',
            'firebase_uid' => 'firebase-booking-user',
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
            'title' => 'Premium Wedding',
            'description' => 'Premium package',
            'price' => 10000,
            'duration' => '6 hours',
            'inclusions' => ['Edited photos', 'Highlights reel'],
            'status' => 'active',
        ]);

        $token = $user->createToken('test-user')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '10:00',
                'address' => '123 Test Street',
                'notes' => 'Please arrive early',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $user->id);
    }

    public function test_partner_cannot_create_a_booking(): void
    {
        $partner = Partner::create([
            'name' => 'Partner User',
            'phone' => '+922222222222',
            'email' => 'partner.user@example.com',
            'firebase_uid' => 'firebase-partner-user',
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
            'title' => 'Portrait Lite',
            'description' => 'Portrait package',
            'price' => 2500,
            'duration' => '1 hour',
            'inclusions' => ['10 edits'],
            'status' => 'active',
        ]);

        $token = $partner->createToken('test-partner')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '14:00',
                'address' => '456 Test Avenue',
            ]);

        $response->assertForbidden();
    }

    public function test_booking_can_move_through_assignment_results_and_final_payment_flow(): void
    {
        $user = User::create([
            'name' => 'Lifecycle User',
            'phone' => '+913333333333',
            'email' => 'lifecycle.user@example.com',
            'firebase_uid' => 'firebase-lifecycle-user',
            'status' => 'active',
        ]);

        $owner = Owner::create([
            'name' => 'Lifecycle Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Lifecycle Partner',
            'phone' => '+914444444444',
            'email' => 'lifecycle.partner@example.com',
            'firebase_uid' => 'firebase-lifecycle-partner',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Maternity',
            'description' => 'Maternity shoots',
            'image' => 'categories/maternity.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Maternity Gold',
            'description' => 'Premium maternity package',
            'price' => 8000,
            'duration' => '3 hours',
            'inclusions' => ['25 edited photos', '1 teaser reel'],
            'status' => 'active',
        ]);

        $userToken = $user->createToken('lifecycle-user')->plainTextToken;
        $ownerToken = $owner->createToken('lifecycle-owner')->plainTextToken;
        $partnerToken = $partner->createToken('lifecycle-partner')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingResponse = $this
            ->withToken($userToken)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '09:30',
                'address' => '789 Lifecycle Road',
                'notes' => 'Ring the bell on arrival',
            ])
            ->assertCreated();

        $bookingId = $bookingResponse->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
                'payment_reference' => 'adv_12345',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_type', 'advance');

        $this->app['auth']->forgetGuards();
        $this->withToken($ownerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/assign-partner", [
                'partner_id' => $partner->id,
                'remarks' => 'Assigning best nearby partner',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'assigned');

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'accepted',
                'remarks' => 'Accepted and preparing gear',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/status", [
                'status' => 'in_progress',
                'remarks' => 'Shoot started',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->app['auth']->forgetGuards();
        $this->withToken($partnerToken)
            ->postJson("/api/v1/bookings/{$bookingId}/upload-results", [
                'results' => [
                    [
                        'file_url' => 'https://example.com/results/photo1.jpg',
                        'file_type' => 'photo',
                        'notes' => 'Hero frame',
                    ],
                ],
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->postJson('/api/v1/payments/final', [
                'booking_id' => $bookingId,
                'payment_reference' => 'fin_67890',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_type', 'final');

        $this->app['auth']->forgetGuards();
        $this->withToken($userToken)
            ->getJson("/api/v1/bookings/{$bookingId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.advance_paid', true)
            ->assertJsonPath('data.final_paid', true);
    }

    public function test_final_payment_requires_uploaded_results(): void
    {
        $user = User::create([
            'name' => 'Payment Gate User',
            'phone' => '+915555555555',
            'email' => 'payment.user@example.com',
            'firebase_uid' => 'firebase-payment-user',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Birthday',
            'description' => 'Birthday shoots',
            'image' => 'categories/birthday.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Birthday Basic',
            'description' => 'Birthday package',
            'price' => 4000,
            'duration' => '2 hours',
            'inclusions' => ['15 edited photos'],
            'status' => 'active',
        ]);

        $token = $user->createToken('payment-user')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $bookingId = $this
            ->withToken($token)
            ->postJson('/api/v1/bookings', [
                'category_id' => $category->id,
                'plan_id' => $plan->id,
                'booking_date' => now()->addWeek()->toDateString(),
                'booking_time' => '16:00',
                'address' => '101 Payment Street',
            ])
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->postJson('/api/v1/payments/advance', [
                'booking_id' => $bookingId,
            ])
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->postJson('/api/v1/payments/final', [
                'booking_id' => $bookingId,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Final payment requires uploaded results.');
    }
}
