<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminCity;
use App\Models\Payment;
use App\Support\AdminCityScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController
{
    use AuthorizesAdminCity;

    public function index(Request $request): Response
    {
        $admin = Auth::guard('admin')->user();
        $filters = $request->only(['booking_id', 'user', 'payment_type', 'payment_status', 'date']);

        $base = AdminCityScope::payments(Payment::query(), $admin);

        $summaryBase = clone $base;

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $base->with(['booking.user', 'booking.plan'])
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Payment $payment) => $this->transformPayment($payment)),
            'filters' => $filters,
            'summary' => [
                'advance_paid' => (string) (clone $summaryBase)->where('payment_type', 'advance')->where('payment_status', 'paid')->sum('amount'),
                'final_paid' => (string) (clone $summaryBase)->where('payment_type', 'final')->where('payment_status', 'paid')->sum('amount'),
                'pending' => (clone $summaryBase)->where('payment_status', 'pending')->count(),
            ],
        ]);
    }

    public function show(Payment $payment): Response
    {
        $this->abortUnlessPaymentInScope($payment);

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
