<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $category = Category::first();
        $plan = Plan::first();
        $partner = Partner::where('status', 'active')->first();

        if (! $user || ! $category || ! $plan) {
            return;
        }

        $booking = Booking::updateOrCreate(
            ['user_id' => $user->id, 'plan_id' => $plan->id, 'booking_date' => now()->addDays(4)->toDateString()],
            [
                'category_id' => $category->id,
                'assigned_partner_id' => $partner?->id,
                'booking_time' => '11:00:00',
                'address' => 'Jubilee Hills, Hyderabad',
                'notes' => 'Outdoor candid coverage preferred',
                'status' => BookingStatus::Assigned->value,
                'total_amount' => $plan->price,
                'advance_amount' => round((float) $plan->price * 0.2, 2),
                'final_amount' => round((float) $plan->price * 0.8, 2),
                'advance_paid' => true,
                'final_paid' => false,
            ]
        );

        $booking->statusLogs()->delete();
        $booking->statusLogs()->createMany([
            ['status' => BookingStatus::Pending->value, 'remarks' => 'Booking created', 'changed_by_type' => 'user', 'changed_by_id' => $user->id],
            ['status' => BookingStatus::Confirmed->value, 'remarks' => 'Advance payment received', 'changed_by_type' => 'system', 'changed_by_id' => 0],
            ['status' => BookingStatus::Assigned->value, 'remarks' => 'Partner assigned', 'changed_by_type' => 'admin', 'changed_by_id' => 1],
        ]);

        Payment::updateOrCreate(
            ['booking_id' => $booking->id, 'payment_type' => PaymentType::Advance->value],
            [
                'amount' => $booking->advance_amount,
                'payment_status' => PaymentStatus::Paid->value,
                'payment_reference' => 'ADV-' . $booking->id,
                'paid_at' => now()->subDay(),
            ]
        );
    }
}
