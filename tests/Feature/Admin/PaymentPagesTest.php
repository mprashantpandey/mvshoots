<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payment_index_and_details(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Mira',
            'phone' => '7777777777',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Pre Wedding',
            'status' => 'active',
        ]);

        $plan = Plan::create([
            'category_id' => $category->id,
            'title' => 'Couple Story',
            'price' => 15000,
            'duration' => '3 hours',
            'inclusions' => ['50 edited photos'],
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'plan_id' => $plan->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '16:00',
            'address' => 'Mumbai',
            'status' => 'confirmed',
            'total_amount' => 15000,
            'advance_amount' => 3000,
            'final_amount' => 12000,
            'advance_paid' => true,
            'final_paid' => false,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'payment_type' => 'advance',
            'amount' => 3000,
            'payment_status' => 'paid',
            'payment_reference' => 'pay_test_123',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.payments.index'))
            ->assertOk();

        $this->get(route('admin.payments.show', $payment))
            ->assertOk();
    }
}
