@extends('admin.layouts.app')

@section('page-title', 'Partner Details')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="row g-4">
            <div class="col-md-3"><strong>Name</strong><div>{{ $partner->name }}</div></div>
            <div class="col-md-3"><strong>Phone</strong><div>{{ $partner->phone }}</div></div>
            <div class="col-md-3"><strong>Email</strong><div>{{ $partner->email ?: 'N/A' }}</div></div>
            <div class="col-md-3"><strong>Status</strong><div><x-admin.status-badge :value="$partner->status" /></div></div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="table-card">
                <div class="p-4 border-bottom"><h2 class="h5 mb-0">Assigned Bookings</h2></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>User</th><th>Plan</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($partner->assignedBookings as $booking)
                                <tr>
                                    <td><a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none fw-semibold">#{{ $booking->id }}</a></td>
                                    <td>{{ $booking->user?->name }}</td>
                                    <td>{{ $booking->plan?->title }}</td>
                                    <td><x-admin.status-badge :value="$booking->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-5 text-secondary">No assigned bookings.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="table-card">
                <div class="p-4 border-bottom"><h2 class="h5 mb-0">Uploaded Results</h2></div>
                <div class="p-4">
                    @forelse($partner->bookingResults as $result)
                        <div class="border rounded-4 p-3 mb-3">
                            <div class="fw-semibold">{{ str($result->file_type)->headline() }}</div>
                            <div class="small text-secondary">{{ $result->notes ?: 'No notes' }}</div>
                        </div>
                    @empty
                        <div class="text-secondary">No result uploads yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
