<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $notifications = [
            ['title' => 'Booking assigned', 'body' => 'A partner has been assigned to your booking.', 'type' => 'booking_assigned'],
            ['title' => 'Final payment pending', 'body' => 'Please complete the remaining payment once results are reviewed.', 'type' => 'final_payment_pending'],
        ];

        foreach ($notifications as $notification) {
            AppNotification::updateOrCreate(
                ['user_type' => 'user', 'user_id' => $user->id, 'title' => $notification['title']],
                [...$notification, 'reference_id' => 1, 'is_read' => false]
            );
        }
    }
}
