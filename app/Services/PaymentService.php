<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly NotificationService $notificationService,
        private readonly PaymentGatewayInterface $paymentGateway,
    ) {
    }

    public function createGatewayOrder(Booking $booking, string $paymentType): array
    {
        $amount = $paymentType === PaymentType::Advance->value
            ? (float) $booking->advance_amount
            : (float) $booking->final_amount;

        return $this->paymentGateway->createOrder($booking, $paymentType, $amount);
    }

    public function recordAdvancePayment(int $bookingId, ?string $reference = null): Payment
    {
        return $this->db->transaction(function () use ($bookingId, $reference): Payment {
            $booking = Booking::findOrFail($bookingId);

            if ($booking->advance_paid) {
                throw new InvalidArgumentException('Advance payment has already been completed.');
            }

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_type' => PaymentType::Advance->value,
                'amount' => $booking->advance_amount,
                'payment_status' => PaymentStatus::Paid->value,
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            $booking->update([
                'advance_paid' => true,
                'status' => BookingStatus::Confirmed->value,
            ]);

            $booking->statusLogs()->create([
                'status' => BookingStatus::Confirmed->value,
                'remarks' => 'Advance payment received',
                'changed_by_type' => 'system',
                'changed_by_id' => 0,
            ]);

            $this->notificationService->create(
                'user',
                (int) $booking->user_id,
                'Advance payment confirmed',
                'Your booking is confirmed and ready for assignment.',
                'advance_paid',
                (int) $booking->id
            );

            return $payment;
        });
    }

    public function recordFinalPayment(int $bookingId, ?string $reference = null): Payment
    {
        return $this->db->transaction(function () use ($bookingId, $reference): Payment {
            $booking = Booking::with('results')->findOrFail($bookingId);

            if (! $booking->advance_paid) {
                throw new InvalidArgumentException('Advance payment is required before final payment.');
            }

            if ($booking->final_paid) {
                throw new InvalidArgumentException('Final payment has already been completed.');
            }

            if ($booking->results->isEmpty()) {
                throw new InvalidArgumentException('Final payment requires uploaded results.');
            }

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_type' => PaymentType::Final->value,
                'amount' => $booking->final_amount,
                'payment_status' => PaymentStatus::Paid->value,
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            $booking->update([
                'final_paid' => true,
                'status' => BookingStatus::Completed->value,
            ]);

            $booking->statusLogs()->create([
                'status' => BookingStatus::Completed->value,
                'remarks' => 'Final payment received',
                'changed_by_type' => 'system',
                'changed_by_id' => 0,
            ]);

            $this->notificationService->create(
                'user',
                (int) $booking->user_id,
                'Booking completed',
                'Your booking has been completed successfully.',
                'booking_completed',
                (int) $booking->id
            );

            if ($booking->assigned_partner_id) {
                $this->notificationService->create(
                    'partner',
                    (int) $booking->assigned_partner_id,
                    'Booking completed',
                    "Booking #{$booking->id} has been marked completed.",
                    'booking_completed',
                    (int) $booking->id
                );
            }

            return $payment;
        });
    }
}
