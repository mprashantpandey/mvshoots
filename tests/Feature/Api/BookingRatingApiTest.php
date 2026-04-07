<?php

namespace Tests\Feature\Api;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\PartnerRating;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRatingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_rate_completed_booking_and_update_rating(): void
    {
        $user = User::create([
            'name' => 'Rater',
            'phone' => '+911122223333',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Rated Partner',
            'phone' => '+911122223334',
            'status' => 'active',
        ]);
        $this->seedVerifiedPartnerKyc($partner);

        $category = Category::create([
            'name' => 'Wedding',
            'description' => 'Wedding',
            'image' => 'categories/w.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Plan',
            'description' => 'Plan',
            'price' => 10000,
            'duration' => '4h',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'address' => 'Addr',
            'status' => BookingStatus::Completed->value,
            'total_amount' => 10000,
            'advance_amount' => 2000,
            'final_amount' => 8000,
            'advance_paid' => true,
            'final_paid' => true,
        ]);

        $token = $user->createToken('rater')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/v1/bookings/{$booking->id}/rating", [
                'rating' => 5,
                'review' => 'Excellent work.',
            ])
            ->assertOk()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.review', 'Excellent work.');

        $this->assertDatabaseHas('partner_ratings', [
            'booking_id' => $booking->id,
            'partner_id' => $partner->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/bookings/{$booking->id}/rating", [
                'rating' => 4,
                'review' => 'Updated review.',
            ])
            ->assertOk()
            ->assertJsonPath('data.rating', 4);

        $this->assertSame(1, PartnerRating::query()->where('booking_id', $booking->id)->count());
    }

    public function test_user_cannot_rate_incomplete_booking(): void
    {
        $user = User::create([
            'name' => 'Rater Two',
            'phone' => '+911122223335',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Partner Two',
            'phone' => '+911122223336',
            'status' => 'active',
        ]);
        $this->seedVerifiedPartnerKyc($partner);

        $category = Category::create([
            'name' => 'Portrait',
            'description' => 'Portrait',
            'image' => 'categories/p.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Portrait Plan',
            'description' => 'Plan',
            'price' => 5000,
            'duration' => '2h',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '11:00',
            'address' => 'Addr',
            'status' => BookingStatus::Confirmed->value,
            'total_amount' => 5000,
            'advance_amount' => 1000,
            'final_amount' => 4000,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        $token = $user->createToken('rater2')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/v1/bookings/{$booking->id}/rating", [
                'rating' => 5,
            ])
            ->assertStatus(422);
    }

    public function test_partner_can_list_ratings_received(): void
    {
        $user = User::create([
            'name' => 'Customer',
            'phone' => '+911122223337',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Partner Three',
            'phone' => '+911122223338',
            'status' => 'active',
        ]);
        $this->seedVerifiedPartnerKyc($partner);

        $category = Category::create([
            'name' => 'Event',
            'description' => 'Event',
            'image' => 'categories/e.jpg',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Event Plan',
            'description' => 'Plan',
            'price' => 8000,
            'duration' => '3h',
            'inclusions' => ['Photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'address' => 'Addr',
            'status' => BookingStatus::Completed->value,
            'total_amount' => 8000,
            'advance_amount' => 1600,
            'final_amount' => 6400,
            'advance_paid' => true,
            'final_paid' => true,
        ]);

        PartnerRating::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'rating' => 5,
            'review' => 'Great',
        ]);

        $token = $partner->createToken('partner-ratings')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/partner/ratings')
            ->assertOk()
            ->assertJsonPath('data.data.0.rating', 5)
            ->assertJsonPath('data.data.0.customer_display_name', 'Customer');
    }
}
