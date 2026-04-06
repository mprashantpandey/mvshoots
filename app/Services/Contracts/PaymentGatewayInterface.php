<?php

namespace App\Services\Contracts;

use App\Models\Booking;

interface PaymentGatewayInterface
{
    public function createOrder(Booking $booking, string $paymentType, float $amount): array;
}
