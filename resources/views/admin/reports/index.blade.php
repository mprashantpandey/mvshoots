@extends('admin.layouts.app')

@section('page-title', 'Reports')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h5 mb-1">Reports & Exports</h2>
                <p class="text-secondary mb-0">Review operational totals, payment health, and partner performance.</p>
            </div>
            <a href="{{ route('admin.reports.index', array_merge($filters, ['export' => 1])) }}" class="btn btn-primary">Export CSV</a>
        </div>
        <form method="GET" class="filter-grid mt-4">
            <input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            <input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            <button class="btn btn-outline-primary">Apply Range</button>
        </form>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><x-admin.stat-card label="Bookings in Range" :value="$totals['bookings']" icon="bi-calendar-range" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Revenue in Range" :value="'₹' . number_format($totals['revenue'], 2)" icon="bi-graph-up-arrow" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Payments in Range" :value="$totals['payments']" icon="bi-cash-stack" /></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="p-4 border-bottom"><h2 class="h5 mb-0">Booking Report</h2></div>
                <div class="p-4">
                    @foreach($bookingStatusCounts as $status => $count)
                        <div class="d-flex justify-content-between align-items-center border rounded-4 p-3 mb-3">
                            <div>{{ str($status)->headline() }}</div>
                            <strong>{{ $count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="p-4 border-bottom"><h2 class="h5 mb-0">Revenue by Payment Type</h2></div>
                <div class="p-4">
                    @foreach($paymentTypeTotals as $type => $total)
                        <div class="d-flex justify-content-between align-items-center border rounded-4 p-3 mb-3">
                            <div>{{ str($type)->headline() }}</div>
                            <strong>₹{{ number_format($total, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="table-card">
                <div class="p-4 border-bottom"><h2 class="h5 mb-0">Partner Performance</h2></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Partner</th><th>Total Assignments</th><th>Completed</th></tr></thead>
                        <tbody>
                            @forelse($partnerPerformance as $partner)
                                <tr>
                                    <td>{{ $partner->name }}</td>
                                    <td>{{ $partner->assigned_bookings_count }}</td>
                                    <td>{{ $partner->completed_bookings_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-5 text-secondary">No partner stats available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
