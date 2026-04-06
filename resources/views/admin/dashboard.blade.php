@extends('admin.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Total Users" :value="$totalUsers" icon="bi-people" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Total Partners" :value="$totalPartners" icon="bi-camera-reels" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Total Bookings" :value="$totalBookings" icon="bi-calendar2-check" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Revenue" :value="'₹' . number_format($totalRevenue, 2)" icon="bi-cash-stack" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Categories" :value="$totalCategories" icon="bi-collection" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Plans" :value="$totalPlans" icon="bi-card-checklist" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Reels" :value="$totalReels" icon="bi-play-btn" /></div>
        <div class="col-6 col-xl-3"><x-admin.stat-card label="Pending Payments" :value="$pendingPayments" icon="bi-hourglass-split" /></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h5 mb-1">Bookings Trend</h2>
                        <p class="text-secondary mb-0">Recent daily booking volume</p>
                    </div>
                    <x-admin.status-badge value="pending" />
                </div>
                <canvas id="bookingsChart" height="110"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h5 mb-1">Revenue Trend</h2>
                        <p class="text-secondary mb-0">Paid payments in the latest 7 days</p>
                    </div>
                    <x-admin.status-badge value="paid" />
                </div>
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><x-admin.stat-card label="Pending Bookings" :value="$pendingBookings" icon="bi-clock-history" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Completed Bookings" :value="$completedBookings" icon="bi-patch-check" /></div>
        <div class="col-md-4"><x-admin.stat-card label="Platform Health" value="Stable" hint="Core operations are connected" icon="bi-activity" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="table-card">
                <div class="p-4 border-bottom">
                    <h2 class="h5 mb-1">Recent Bookings</h2>
                    <p class="text-secondary mb-0">Latest user orders and assignment status</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Partner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $booking)
                                <tr>
                                    <td><a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none fw-semibold">#{{ $booking->id }}</a></td>
                                    <td>{{ $booking->user?->name }}</td>
                                    <td>{{ $booking->plan?->title }}</td>
                                    <td><x-admin.status-badge :value="$booking->status" /></td>
                                    <td>{{ $booking->assignedPartner?->name ?? 'Unassigned' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-5">No recent bookings.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="table-card">
                <div class="p-4 border-bottom">
                    <h2 class="h5 mb-1">Recent Payments</h2>
                    <p class="text-secondary mb-0">Advance and final payment activity</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td><a href="{{ route('admin.payments.show', $payment) }}" class="text-decoration-none fw-semibold">#{{ $payment->booking_id }}</a></td>
                                    <td>{{ $payment->booking?->user?->name }}</td>
                                    <td><x-admin.status-badge :value="$payment->payment_type" /></td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-5">No recent payments.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        new Chart(document.getElementById('bookingsChart'), {
            type: 'line',
            data: {
                labels: @json($bookingChart->pluck('date')),
                datasets: [{
                    label: 'Bookings',
                    data: @json($bookingChart->pluck('total')),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: @json($revenueChart->pluck('date')),
                datasets: [{
                    label: 'Revenue',
                    data: @json($revenueChart->pluck('total')),
                    backgroundColor: '#10b981',
                    borderRadius: 10
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    </script>
@endpush
