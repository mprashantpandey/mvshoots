<?php

namespace App\Services\PaymentGateways;

use App\Models\Booking;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class RazorpayPaymentGateway implements PaymentGatewayInterface
{
    public function createOrder(Booking $booking, string $paymentType, float $amount): array
    {
        $amountInSubunits = (int) round($amount * 100);
        $receipt = sprintf('booking_%d_%s_%s', $booking->id, $paymentType, Str::random(6));

        $response = Http::withBasicAuth(
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret'),
        )->post('https://api.razorpay.com/v1/orders', [
            'amount' => $amountInSubunits,
            'currency' => 'INR',
            'receipt' => $receipt,
            'notes' => [
                'booking_id' => (string) $booking->id,
                'payment_type' => $paymentType,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Could not create Razorpay order.');
        }

        $order = $response->json();

        return [
            'gateway' => 'razorpay',
            'booking_id' => $booking->id,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'amount_subunits' => $amountInSubunits,
            'currency' => 'INR',
            'order_id' => $order['id'] ?? null,
            'key_id' => config('services.razorpay.key_id'),
            'merchant_name' => config('services.razorpay.merchant_name', config('app.name')),
            'logo_url' => config('services.razorpay.logo_url'),
            'description' => sprintf('Booking #%d %s payment', $booking->id, ucfirst($paymentType)),
            'prefill' => [
                'name' => $booking->user?->name,
                'contact' => $booking->user?->phone,
                'email' => $booking->user?->email,
            ],
        ];
    }

    public function verifyPayment(array $payload): bool
    {
        $orderId = $payload['gateway_order_id'] ?? null;
        $paymentId = $payload['gateway_payment_id'] ?? null;
        $signature = $payload['gateway_signature'] ?? null;

        if (! $orderId || ! $paymentId || ! $signature) {
            return false;
        }

        $generated = hash_hmac(
            'sha256',
            $orderId.'|'.$paymentId,
            (string) config('services.razorpay.key_secret')
        );

        return hash_equals($generated, $signature);
    }
}
