@extends('admin.layouts.app')

@section('page-title', 'Booking Details')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h2 class="h4 mb-1">Booking #{{ $booking->id }}</h2>
                        <p class="text-secondary mb-0">{{ $booking->plan?->title }} • {{ $booking->category?->name }}</p>
                    </div>
                    <x-admin.status-badge :value="$booking->status" />
                </div>
                <div class="row g-4 mt-1">
                    <div class="col-md-6"><strong>Customer</strong><div>{{ $booking->user?->name }}<br>{{ $booking->user?->phone }}</div></div>
                    <div class="col-md-6"><strong>Assigned Partner</strong><div>{{ $booking->assignedPartner?->name ?? 'Not assigned yet' }}</div></div>
                    <div class="col-md-6"><strong>Shoot Date</strong><div>{{ $booking->booking_date?->format('d M Y') }}</div></div>
                    <div class="col-md-6"><strong>Shoot Time</strong><div>{{ $booking->booking_time }}</div></div>
                    <div class="col-12"><strong>Address</strong><div>{{ $booking->address }}</div></div>
                    <div class="col-12"><strong>Notes</strong><div>{{ $booking->notes ?: 'No notes provided.' }}</div></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="glass-card p-4 mb-4">
                <h3 class="h6 mb-3">Assign Partner</h3>
                <form method="POST" action="{{ route('admin.bookings.assign-partner', $booking) }}">
                    @csrf
                    <select class="form-select mb-3" name="partner_id" required>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                        @endforeach
                    </select>
                    <textarea class="form-control mb-3" name="remarks" placeholder="Remarks"></textarea>
                    <button class="btn btn-primary w-100">Assign Partner</button>
                </form>
            </div>
            <div class="glass-card p-4">
                <h3 class="h6 mb-3">Update Status</h3>
                <form method="POST" action="{{ route('admin.bookings.update-status', $booking) }}">
                    @csrf
                    <select class="form-select mb-3" name="status" required>
                        @foreach(\App\Enums\BookingStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($booking->status === $status->value)>{{ str($status->value)->headline() }}</option>
                        @endforeach
                    </select>
                    <textarea class="form-control mb-3" name="remarks" placeholder="Remarks"></textarea>
                    <button class="btn btn-outline-primary w-100">Update Booking</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-4 border-bottom"><h3 class="h6 mb-0">Payment Summary</h3></div>
                <div class="p-4">
                    <div class="d-flex justify-content-between mb-2"><span>Total Amount</span><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Advance</span><strong>₹{{ number_format($booking->advance_amount, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Final</span><strong>₹{{ number_format($booking->final_amount, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Advance Paid</span><x-admin.status-badge :value="$booking->advance_paid ? 'paid' : 'pending'" /></div>
                    <div class="d-flex justify-content-between"><span>Final Paid</span><x-admin.status-badge :value="$booking->final_paid ? 'paid' : 'pending'" /></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-4 border-bottom"><h3 class="h6 mb-0">Status Timeline</h3></div>
                <div class="p-4">
                    @forelse($booking->statusLogs as $log)
                        <div class="border-start border-3 border-primary ps-3 pb-3 mb-3">
                            <div class="fw-semibold">{{ str($log->status)->headline() }}</div>
                            <div class="small text-secondary">{{ $log->remarks ?: 'No remarks' }}</div>
                            <div class="small text-secondary mt-1">{{ $log->created_at }}</div>
                        </div>
                    @empty
                        <div class="text-secondary">No status logs yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="table-card h-100">
                <div class="p-4 border-bottom"><h3 class="h6 mb-0">Uploaded Results</h3></div>
                <div class="p-4">
                    @forelse($booking->results as $result)
                        <div class="border rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ str($result->file_type)->headline() }}</strong>
                                <span class="small text-secondary">{{ $result->partner?->name }}</span>
                            </div>
                            <div class="small text-secondary mb-2">{{ $result->notes ?: 'No notes' }}</div>
                            <a href="{{ $result->file_url }}" target="_blank" class="small text-decoration-none">View file</a>
                        </div>
                    @empty
                        <div class="text-secondary">No results uploaded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
