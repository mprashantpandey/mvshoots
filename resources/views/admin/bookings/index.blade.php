@extends('admin.layouts.app')

@section('page-title', 'Bookings')

@section('content')
    <div class="glass-card p-4 mb-4">
        <h2 class="h5 mb-3">Booking Management</h2>
        <form method="GET" class="filter-grid">
            <input class="form-control" name="booking_id" value="{{ $filters['booking_id'] ?? '' }}" placeholder="Booking ID">
            <input class="form-control" name="user" value="{{ $filters['user'] ?? '' }}" placeholder="User">
            <select class="form-select" name="partner_id">
                <option value="">All partners</option>
                @foreach($partners as $partner)
                    <option value="{{ $partner->id }}" @selected((string)($filters['partner_id'] ?? '') === (string)$partner->id)>{{ $partner->name }}</option>
                @endforeach
            </select>
            <select class="form-select" name="category_id">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)($filters['category_id'] ?? '') === (string)$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="form-select" name="plan_id">
                <option value="">All plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" @selected((string)($filters['plan_id'] ?? '') === (string)$plan->id)>{{ $plan->title }}</option>
                @endforeach
            </select>
            <input class="form-control" type="date" name="date" value="{{ $filters['date'] ?? '' }}">
            <select class="form-select" name="payment_status">
                <option value="">All payment states</option>
                <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Pending</option>
                <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
            </select>
            <select class="form-select" name="status">
                <option value="">All booking statuses</option>
                @foreach(\App\Enums\BookingStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ str($status->value)->headline() }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary">Apply Filters</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>ID</th><th>User</th><th>Category</th><th>Plan</th><th>Date</th><th>Status</th><th>Partner</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="fw-semibold">#{{ $booking->id }}</td>
                            <td>{{ $booking->user?->name }}</td>
                            <td>{{ $booking->category?->name }}</td>
                            <td>{{ $booking->plan?->title }}</td>
                            <td>{{ $booking->booking_date?->format('d M Y') }}<br><span class="text-secondary small">{{ $booking->booking_time }}</span></td>
                            <td><x-admin.status-badge :value="$booking->status" /></td>
                            <td>{{ $booking->assignedPartner?->name ?? 'Unassigned' }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.bookings.show', $booking) }}">Details</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-secondary">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $bookings->links() }}</div>
    </div>
@endsection
