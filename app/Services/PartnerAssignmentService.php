<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use InvalidArgumentException;

class PartnerAssignmentService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function assign(Booking $booking, int $partnerId, mixed $actor, ?string $remarks = null): Booking
    {
        if ($booking->status !== BookingStatus::Confirmed->value) {
            throw new InvalidArgumentException('Only confirmed bookings can be assigned.');
        }

        $booking->update([
            'assigned_partner_id' => $partnerId,
            'status' => BookingStatus::Assigned->value,
        ]);

        $booking->statusLogs()->create([
            'status' => BookingStatus::Assigned->value,
            'remarks' => $remarks ?? 'Partner assigned',
            'changed_by_type' => str(class_basename($actor))->lower()->value(),
            'changed_by_id' => (int) ($actor->id ?? 0),
        ]);

        $this->notificationService->create(
            'partner',
            $partnerId,
            'New order assigned',
            "You have a new order: booking #{$booking->id}.",
            'booking_assigned',
            (int) $booking->id
        );

        $this->notificationService->create(
            'user',
            (int) $booking->user_id,
            'Partner assigned',
            'A partner has been assigned to your booking.',
            'booking_assigned',
            (int) $booking->id
        );

        return $booking->fresh();
    }
}
