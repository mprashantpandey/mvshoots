<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_assign_and_update_booking_status(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Aarav',
            'phone' => '9999999999',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Wedding',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Signature Wedding',
            'price' => 20000,
            'duration' => '6 hours',
            'inclusions' => ['100 edited photos'],
            'status' => 'active',
        ]);

        $partner = Partner::create([
            'name' => 'Studio Partner',
            'phone' => '8888888888',
            'status' => 'active',
        ]);
        $this->seedVerifiedPartnerKyc($partner);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'address' => 'Pune',
            'status' => BookingStatus::Confirmed->value,
            'total_amount' => 20000,
            'advance_amount' => 4000,
            'final_amount' => 16000,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.bookings.index'))
            ->assertOk();

        $this->get(route('admin.bookings.show', $booking))
            ->assertOk();

        $assignResponse = $this->post(route('admin.bookings.assign-partner', $booking), [
            'partner_id' => $partner->id,
            'remarks' => 'Assigning the best available partner.',
        ]);

        $assignResponse->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame($partner->id, $booking->assigned_partner_id);
        $this->assertSame(BookingStatus::Assigned->value, $booking->status);
        $this->assertDatabaseHas('notifications', [
            'user_type' => 'partner',
            'user_id' => $partner->id,
            'type' => 'booking_assigned',
            'reference_id' => $booking->id,
            'title' => 'New order assigned',
        ]);

        $statusResponse = $this->post(route('admin.bookings.update-status', $booking), [
            'status' => BookingStatus::Accepted->value,
            'remarks' => 'Partner accepted from admin panel.',
        ]);

        $statusResponse->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingStatus::Accepted->value, $booking->status);
        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'status' => BookingStatus::Accepted->value,
        ]);
    }
}
