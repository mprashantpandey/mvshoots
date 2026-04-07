<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingResult;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly NotificationService $notificationService,
    ) {}

    public function create(array $data): Booking
    {
        return $this->db->transaction(function () use ($data): Booking {
            $plan = Plan::findOrFail($data['plan_id']);
            $totalAmount = (float) $plan->price;
            $advancePercentage = $this->bookingAdvancePercentage();
            $advanceRatio = $advancePercentage / 100;

            $booking = Booking::create([
                ...$data,
                'status' => BookingStatus::Pending->value,
                'total_amount' => $totalAmount,
                'advance_amount' => round($totalAmount * $advanceRatio, 2),
                'final_amount' => round($totalAmount - round($totalAmount * $advanceRatio, 2), 2),
                'advance_paid' => false,
                'final_paid' => false,
            ]);

            $booking->statusLogs()->create([
                'status' => BookingStatus::Pending->value,
                'remarks' => 'Booking created',
                'changed_by_type' => 'user',
                'changed_by_id' => (int) $booking->user_id,
            ]);

            $this->notificationService->create(
                'user',
                (int) $booking->user_id,
                'Booking created',
                'Your booking has been created and is awaiting advance payment confirmation.',
                'booking_created',
                (int) $booking->id
            );

            $this->notificationService->notifyOperators(
                'New booking received',
                "Booking #{$booking->id} has been created and is awaiting advance payment.",
                'booking_created',
                (int) $booking->id
            );

            return $booking;
        });
    }

    public function updateStatus(Booking $booking, string $status, mixed $actor, ?string $remarks = null): Booking
    {
        if (! in_array($status, BookingStatus::values(), true)) {
            throw new InvalidArgumentException('Invalid booking status.');
        }

        $allowed = [
            BookingStatus::Assigned->value => [BookingStatus::Confirmed->value],
            BookingStatus::Accepted->value => [BookingStatus::Assigned->value],
            BookingStatus::InProgress->value => [BookingStatus::Accepted->value],
            BookingStatus::Completed->value => [BookingStatus::InProgress->value],
        ];

        if (isset($allowed[$status]) && ! in_array($booking->status, $allowed[$status], true)) {
            throw new InvalidArgumentException('Invalid booking status transition.');
        }

        if ($status === BookingStatus::Completed->value && (! $booking->final_paid || $booking->results()->count() === 0)) {
            throw new InvalidArgumentException('Booking requires final payment and results before completion.');
        }

        $booking->update(['status' => $status]);

        $booking->statusLogs()->create([
            'status' => $status,
            'remarks' => $remarks,
            'changed_by_type' => str(class_basename($actor))->lower()->value(),
            'changed_by_id' => (int) ($actor->id ?? 0),
        ]);

        if ($booking->assigned_partner_id) {
            $this->notificationService->create(
                'partner',
                (int) $booking->assigned_partner_id,
                'Booking status updated',
                "Booking #{$booking->id} moved to {$status}.",
                'booking_status_changed',
                (int) $booking->id
            );
        }

        $this->notificationService->create(
            'user',
            (int) $booking->user_id,
            'Booking status updated',
            "Booking #{$booking->id} moved to {$status}.",
            'booking_status_changed',
            (int) $booking->id
        );

        $this->notificationService->notifyOperators(
            'Booking status updated',
            "Booking #{$booking->id} moved to {$status}.",
            'booking_status_changed',
            (int) $booking->id
        );

        return $booking->fresh();
    }

    public function uploadResults(Booking $booking, int $partnerId, array $results): array
    {
        $created = [];

        foreach ($results as $result) {
            $created[] = BookingResult::create([
                'booking_id' => $booking->id,
                'file_url' => $result['file_url'],
                'file_type' => $result['file_type'],
                'uploaded_by_partner_id' => $partnerId,
                'notes' => $result['notes'] ?? null,
            ]);
        }

        $this->notificationService->create(
            'user',
            (int) $booking->user_id,
            'Results uploaded',
            'Your booking results are ready. Please complete the final payment.',
            'results_uploaded',
            (int) $booking->id
        );

        $this->notificationService->create(
            'user',
            (int) $booking->user_id,
            'Final payment pending',
            'Please complete the remaining balance payment for your booking.',
            'final_payment_pending',
            (int) $booking->id
        );

        $this->notificationService->notifyOperators(
            'Results uploaded',
            "Booking #{$booking->id} now has uploaded results.",
            'results_uploaded',
            (int) $booking->id
        );

        return $created;
    }

    private function bookingAdvancePercentage(): float
    {
        $configured = (float) Setting::value('booking_advance_percentage', 20);

        return max(1, min(100, $configured));
    }
}
