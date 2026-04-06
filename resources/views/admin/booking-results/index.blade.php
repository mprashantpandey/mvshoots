@extends('admin.layouts.app')

@section('page-title', 'Booking Results')

@section('content')
    <div class="glass-card p-4 mb-4">
        <h2 class="h5 mb-3">Result Upload Tracking</h2>
        <form method="GET" class="filter-grid">
            <select class="form-select" name="status">
                <option value="">All result states</option>
                <option value="uploaded" @selected(request('status') === 'uploaded')>Uploaded</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            </select>
            <button class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Booking</th><th>User</th><th>Partner</th><th>Result State</th><th>Files</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>{{ $booking->user?->name }}</td>
                            <td>{{ $booking->assignedPartner?->name ?? 'Unassigned' }}</td>
                            <td><x-admin.status-badge :value="$booking->results->isNotEmpty() ? 'completed' : 'pending'" /></td>
                            <td>{{ $booking->results->count() }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.booking-results.show', $booking) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-secondary">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $bookings->links() }}</div>
    </div>
@endsection
