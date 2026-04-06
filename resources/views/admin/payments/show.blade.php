@extends('admin.layouts.app')

@section('page-title', 'Payment Details')

@section('content')
    <div class="glass-card p-4">
        <div class="row g-4">
            <div class="col-md-4"><strong>Booking</strong><div>#{{ $payment->booking_id }}</div></div>
            <div class="col-md-4"><strong>User</strong><div>{{ $payment->booking?->user?->name }}</div></div>
            <div class="col-md-4"><strong>Partner</strong><div>{{ $payment->booking?->assignedPartner?->name ?? 'Unassigned' }}</div></div>
            <div class="col-md-4"><strong>Type</strong><div><x-admin.status-badge :value="$payment->payment_type" /></div></div>
            <div class="col-md-4"><strong>Status</strong><div><x-admin.status-badge :value="$payment->payment_status" /></div></div>
            <div class="col-md-4"><strong>Amount</strong><div>₹{{ number_format($payment->amount, 2) }}</div></div>
            <div class="col-md-6"><strong>Reference</strong><div>{{ $payment->payment_reference ?: 'N/A' }}</div></div>
            <div class="col-md-6"><strong>Paid At</strong><div>{{ $payment->paid_at ?: 'Pending' }}</div></div>
        </div>
    </div>
@endsection
