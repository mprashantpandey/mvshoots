<?php

namespace App\Services\PaymentGateways;

use App\Models\Booking;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class StubPaymentGateway implements PaymentGatewayInterface
{
    public function createOrder(Booking $booking, string $paymentType, float $amount): array
    {
        return [
            'gateway' => 'stub',
            'booking_id' => $booking->id,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'currency' => 'INR',
            'order_id' => 'stub_' . Str::uuid(),
        ];
    }

    public function verifyPayment(array $payload): bool
    {
        return true;
    }
}
