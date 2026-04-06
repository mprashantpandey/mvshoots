<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingResult;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingResultPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_booking_result_pages(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Sara',
            'phone' => '9000000001',
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Frame House',
            'phone' => '9000000002',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Maternity',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Maternity Moments',
            'price' => 12000,
            'duration' => '2 hours',
            'inclusions' => ['30 edited photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '14:00',
            'address' => 'Jaipur',
            'status' => 'in_progress',
            'total_amount' => 12000,
            'advance_amount' => 2400,
            'final_amount' => 9600,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        BookingResult::create([
            'booking_id' => $booking->id,
            'file_url' => 'https://example.com/result.jpg',
            'file_type' => 'photo',
            'uploaded_by_partner_id' => $partner->id,
            'notes' => 'Edited preview',
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.booking-results.index', ['search' => 'Sara']))
            ->assertOk();

        $this->get(route('admin.booking-results.show', $booking))
            ->assertOk();
    }
}
