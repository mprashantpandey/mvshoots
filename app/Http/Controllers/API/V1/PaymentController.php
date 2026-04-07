<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PaymentResource;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function advanceIntent(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can create advance payment intents.');

        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        abort_unless((int) $booking->user_id === (int) $actor->id, 403, 'You can only pay for your own bookings.');

        return $this->success(
            $this->paymentService->createGatewayOrder($booking, 'advance'),
            'Advance payment intent created'
        );
    }

    public function finalIntent(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can create final payment intents.');

        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        abort_unless((int) $booking->user_id === (int) $actor->id, 403, 'You can only pay for your own bookings.');

        return $this->success(
            $this->paymentService->createGatewayOrder($booking, 'final'),
            'Final payment intent created'
        );
    }

    public function payAdvance(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can pay for bookings.');

        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'gateway_order_id' => ['nullable', 'string', 'max:255'],
            'gateway_payment_id' => ['nullable', 'string', 'max:255'],
            'gateway_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        abort_unless((int) $booking->user_id === (int) $actor->id, 403, 'You can only pay for your own bookings.');

        $this->assertValidGatewayPayload($data);

        $payment = $this->paymentService->recordAdvancePayment($data['booking_id'], $data['payment_reference'] ?? null);

        return $this->success(new PaymentResource($payment), 'Advance payment recorded');
    }

    public function payFinal(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can pay for bookings.');

        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'gateway_order_id' => ['nullable', 'string', 'max:255'],
            'gateway_payment_id' => ['nullable', 'string', 'max:255'],
            'gateway_signature' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        abort_unless((int) $booking->user_id === (int) $actor->id, 403, 'You can only pay for your own bookings.');

        $this->assertValidGatewayPayload($data);

        $payment = $this->paymentService->recordFinalPayment($data['booking_id'], $data['payment_reference'] ?? null);

        return $this->success(new PaymentResource($payment), 'Final payment recorded');
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $actor = $request->user('sanctum');

        $allowed = ($actor instanceof User && (int) $booking->user_id === (int) $actor->id)
            || ($actor instanceof Partner && (int) $booking->assigned_partner_id === (int) $actor->id)
            || $actor instanceof Owner
            || $actor instanceof Admin;

        abort_unless($allowed, 403, 'You are not allowed to view payments for this booking.');

        $payments = $booking->payments()->latest()->get();

        return $this->success(PaymentResource::collection($payments), 'Payments fetched');
    }

    private function assertValidGatewayPayload(array $data): void
    {
        $hasGatewayPayload = filled($data['gateway_order_id'] ?? null)
            || filled($data['gateway_payment_id'] ?? null)
            || filled($data['gateway_signature'] ?? null);

        if (! $hasGatewayPayload) {
            return;
        }

        abort_unless(
            $this->paymentService->verifyGatewayPayment($data),
            422,
            'Payment verification failed.'
        );
    }
}
