@extends('admin.layouts.app')

@section('page-title', 'Payments')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4"><x-admin.stat-card label="Advance Collected" :value="'₹' . number_format($summary['advance_paid'], 2)" icon="bi-wallet2" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Final Collected" :value="'₹' . number_format($summary['final_paid'], 2)" icon="bi-cash-coin" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Pending Payments" :value="$summary['pending']" icon="bi-hourglass-split" /></div>
    </div>
    <div class="glass-card p-4 mb-4">
        <h2 class="h5 mb-3">Payment Tracking</h2>
        <form method="GET" class="filter-grid">
            <input class="form-control" name="booking_id" value="{{ $filters['booking_id'] ?? '' }}" placeholder="Booking ID">
            <input class="form-control" name="user" value="{{ $filters['user'] ?? '' }}" placeholder="User">
            <select class="form-select" name="payment_type">
                <option value="">All types</option>
                <option value="advance" @selected(($filters['payment_type'] ?? '') === 'advance')>Advance</option>
                <option value="final" @selected(($filters['payment_type'] ?? '') === 'final')>Final</option>
            </select>
            <select class="form-select" name="payment_status">
                <option value="">All statuses</option>
                <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Pending</option>
                <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
                <option value="failed" @selected(($filters['payment_status'] ?? '') === 'failed')>Failed</option>
            </select>
            <input class="form-control" type="date" name="date" value="{{ $filters['date'] ?? '' }}">
            <button class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Booking</th><th>User</th><th>Type</th><th>Status</th><th>Amount</th><th>Paid At</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>#{{ $payment->booking_id }}</td>
                            <td>{{ $payment->booking?->user?->name }}</td>
                            <td><x-admin.status-badge :value="$payment->payment_type" /></td>
                            <td><x-admin.status-badge :value="$payment->payment_status" /></td>
                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->paid_at ?: 'Pending' }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.payments.show', $payment) }}">Details</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-secondary">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $payments->links() }}</div>
    </div>
@endsection
