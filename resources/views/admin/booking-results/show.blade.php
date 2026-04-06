@extends('admin.layouts.app')

@section('page-title', 'Booking Result Details')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h2 class="h4 mb-1">Booking #{{ $booking->id }}</h2>
                <p class="text-secondary mb-0">{{ $booking->user?->name }} • {{ $booking->assignedPartner?->name ?? 'No partner assigned' }}</p>
            </div>
            <x-admin.status-badge :value="$booking->results->isNotEmpty() ? 'completed' : 'pending'" />
        </div>
    </div>
    <div class="row g-4">
        @forelse($booking->results as $result)
            <div class="col-md-6 col-xl-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>{{ str($result->file_type)->headline() }}</strong>
                        <span class="small text-secondary">{{ $result->partner?->name }}</span>
                    </div>
                    <p class="text-secondary">{{ $result->notes ?: 'No notes added.' }}</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ $result->file_url }}" target="_blank">Open File</a>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="glass-card p-5 text-center text-secondary">No results uploaded for this booking yet.</div></div>
        @endforelse
    </div>
@endsection
