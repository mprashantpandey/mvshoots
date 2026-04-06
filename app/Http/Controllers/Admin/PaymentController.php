<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['booking_id', 'user', 'payment_type', 'payment_status', 'date']);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => Payment::with(['booking.user', 'booking.plan'])
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Payment $payment) => $this->transformPayment($payment)),
            'filters' => $filters,
            'summary' => [
                'advance_paid' => (string) Payment::where('payment_type', 'advance')->where('payment_status', 'paid')->sum('amount'),
                'final_paid' => (string) Payment::where('payment_type', 'final')->where('payment_status', 'paid')->sum('amount'),
                'pending' => Payment::where('payment_status', 'pending')->count(),
            ],
        ]);
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['booking.user', 'booking.plan', 'booking.assignedPartner']);

        return Inertia::render('Admin/Payments/Show', [
            'payment' => $this->transformPayment($payment, true),
        ]);
    }

    private function transformPayment(Payment $payment, bool $detailed = false): array
    {
        $payload = [
            'id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'user_name' => $payment->booking?->user?->name,
            'partner_name' => $payment->booking?->assignedPartner?->name,
            'plan_name' => $payment->booking?->plan?->title,
            'payment_type' => $payment->payment_type,
            'payment_status' => $payment->payment_status,
            'amount' => (string) $payment->amount,
            'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
            'show_url' => route('admin.payments.show', $payment),
        ];

        if ($detailed) {
            $payload['payment_reference'] = $payment->payment_reference;
        }

        return $payload;
    }
}
