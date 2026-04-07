<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Chart } from 'chart.js/auto';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import StatCard from '../../Components/Admin/StatCard.vue';
import StatusBadge from '../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    totalUsers: Number,
    totalPartners: Number,
    totalBookings: Number,
    totalCategories: Number,
    totalPlans: Number,
    totalReels: Number,
    totalRevenue: Number,
    pendingBookings: Number,
    completedBookings: Number,
    pendingPayments: Number,
    pendingKycCount: Number,
    recentBookings: Array,
    recentPayments: Array,
    bookingChart: Array,
    revenueChart: Array,
});

const bookingsCanvas = ref(null);
const revenueCanvas = ref(null);
let bookingsChartInstance = null;
let revenueChartInstance = null;

onMounted(() => {
    bookingsChartInstance = new Chart(bookingsCanvas.value, {
        type: 'line',
        data: {
            labels: props.bookingChart.map((item) => item.date),
            datasets: [{
                label: 'Bookings',
                data: props.bookingChart.map((item) => item.total),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
        },
    });

    revenueChartInstance = new Chart(revenueCanvas.value, {
        type: 'bar',
        data: {
            labels: props.revenueChart.map((item) => item.date),
            datasets: [{
                label: 'Revenue',
                data: props.revenueChart.map((item) => item.total),
                backgroundColor: '#10b981',
                borderRadius: 10,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
        },
    });
});

onBeforeUnmount(() => {
    bookingsChartInstance?.destroy();
    revenueChartInstance?.destroy();
});
</script>

<template>
    <AdminLayout title="Dashboard" subtitle="Customer journey &amp; business health at a glance">
        <div class="row g-4 mb-4">
            <div class="col-6 col-xl-3"><StatCard label="Customers (users)" :value="props.totalUsers" icon="bi-people" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Total Partners" :value="props.totalPartners" icon="bi-camera-reels" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Total Bookings" :value="props.totalBookings" icon="bi-calendar2-check" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Revenue" :value="`₹${Number(props.totalRevenue ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`" icon="bi-cash-stack" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Categories" :value="props.totalCategories" icon="bi-collection" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Plans" :value="props.totalPlans" icon="bi-card-checklist" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Reels" :value="props.totalReels" icon="bi-play-btn" /></div>
            <div class="col-6 col-xl-3"><StatCard label="Pending Payments" :value="props.pendingPayments" icon="bi-hourglass-split" /></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="h5 mb-1">Bookings Trend</h2>
                            <p class="text-secondary mb-0">Recent daily booking volume</p>
                        </div>
                        <StatusBadge value="pending" />
                    </div>
                    <canvas ref="bookingsCanvas" height="110"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="h5 mb-1">Revenue Trend</h2>
                            <p class="text-secondary mb-0">Paid payments in the latest 7 days</p>
                        </div>
                        <StatusBadge value="paid" />
                    </div>
                    <canvas ref="revenueCanvas" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4"><StatCard label="Pending Bookings" :value="props.pendingBookings" icon="bi-clock-history" /></div>
            <div class="col-md-4"><StatCard label="Completed Bookings" :value="props.completedBookings" icon="bi-patch-check" /></div>
            <div class="col-md-4">
                <Link href="/admin/partners/kyc/pending" class="text-decoration-none text-reset d-block h-100 stat-card-link">
                    <StatCard
                        label="Partner KYC pending"
                        :value="props.pendingKycCount"
                        icon="bi-shield-check"
                        hint="Tap to open review queue"
                    />
                </Link>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="table-card">
                    <div class="p-4 border-bottom">
                        <h2 class="h5 mb-1">Recent bookings</h2>
                        <p class="text-secondary mb-0">What customers booked and where each order stands</p>
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
                                <tr v-for="booking in recentBookings" :key="booking.id">
                                    <td><Link :href="booking.show_url" class="text-decoration-none fw-semibold">#{{ booking.id }}</Link></td>
                                    <td>{{ booking.user_name }}</td>
                                    <td>{{ booking.plan_title }}</td>
                                    <td><StatusBadge :value="booking.status" /></td>
                                    <td>{{ booking.partner_name }}</td>
                                </tr>
                                <tr v-if="!recentBookings.length">
                                    <td colspan="5" class="text-center text-secondary py-5">No recent bookings.</td>
                                </tr>
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
                                <tr v-for="payment in recentPayments" :key="payment.id">
                                    <td><Link :href="payment.show_url" class="text-decoration-none fw-semibold">#{{ payment.booking_id }}</Link></td>
                                    <td>{{ payment.user_name }}</td>
                                    <td><StatusBadge :value="payment.payment_type" /></td>
                                    <td>₹{{ Number(payment.amount ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                                </tr>
                                <tr v-if="!recentPayments.length">
                                    <td colspan="4" class="text-center text-secondary py-5">No recent payments.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
