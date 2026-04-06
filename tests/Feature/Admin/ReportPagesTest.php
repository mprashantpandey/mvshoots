<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_and_export_csv(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $partner = Partner::create([
            'name' => 'Visual Crew',
            'phone' => '9000000004',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Neha',
            'phone' => '9000000005',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Birthday',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Birthday Bash',
            'price' => 10000,
            'duration' => '3 hours',
            'inclusions' => ['40 edited photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'assigned_partner_id' => $partner->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '18:00',
            'address' => 'Goa',
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

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.reports.index'))
            ->assertOk();

        $this->get(route('admin.reports.index', ['export' => 1]))
            ->assertOk()
            ->assertHeader('content-disposition');
    }
}
