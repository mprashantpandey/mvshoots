@extends('admin.layouts.app')

@section('page-title', 'User Details')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="row g-4">
            <div class="col-md-4"><strong>Name</strong><div>{{ $user->name }}</div></div>
            <div class="col-md-4"><strong>Phone</strong><div>{{ $user->phone }}</div></div>
            <div class="col-md-4"><strong>Status</strong><div><x-admin.status-badge :value="$user->status" /></div></div>
            <div class="col-12"><strong>Email</strong><div>{{ $user->email ?: 'No email' }}</div></div>
        </div>
    </div>
    <div class="table-card">
        <div class="p-4 border-bottom">
            <h2 class="h5 mb-1">Booking History</h2>
            <p class="text-secondary mb-0">Recent bookings made by this customer.</p>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Plan</th><th>Status</th><th>Partner</th><th>Amount</th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none fw-semibold">#{{ $booking->id }}</a></td>
                            <td>{{ $booking->plan?->title }}</td>
                            <td><x-admin.status-badge :value="$booking->status" /></td>
                            <td>{{ $booking->assignedPartner?->name ?? 'Unassigned' }}</td>
                            <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-secondary">No bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $bookings->links() }}</div>
    </div>
@endsection
