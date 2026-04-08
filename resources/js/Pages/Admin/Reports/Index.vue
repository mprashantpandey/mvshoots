<script setup>
import { computed, reactive } from 'vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatCard from '../../../Components/Admin/StatCard.vue';

const props = defineProps({
    bookingStatusCounts: Array,
    paymentTypeTotals: Array,
    totals: Object,
    partnerPerformance: Array,
    filters: Object,
});

const filters = reactive({
    from: props.filters?.from ?? '',
    to: props.filters?.to ?? '',
});

const exportUrl = computed(() => {
    const params = new URLSearchParams();

    if (filters.from) params.set('from', filters.from);
    if (filters.to) params.set('to', filters.to);
    params.set('export', '1');

    return `/admin/reports?${params.toString()}`;
});

function headline(value) {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>
    <AdminLayout title="Reports">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="h5 mb-1">Reports & Exports</h2>
                    <p class="text-secondary mb-0">Review operational totals, payment health, and partner performance.</p>
                </div>
                <a :href="exportUrl" class="btn btn-primary">Export CSV</a>
            </div>
            <form method="GET" class="filter-grid mt-4">
                <input v-model="filters.from" class="form-control" type="date" name="from">
                <input v-model="filters.to" class="form-control" type="date" name="to">
                <button class="btn btn-outline-primary" type="submit">Apply Range</button>
            </form>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3"><StatCard label="Bookings in Range" :value="totals.bookings" icon="bi-calendar-range" /></div>
            <div class="col-md-6 col-xl-3"><StatCard label="Platform (advance) in Range" :value="`₹${totals.platform_revenue}`" icon="bi-bank" hint="Fixed % — main admin share" /></div>
            <div class="col-md-6 col-xl-3"><StatCard label="Partner earnings (final) in Range" :value="`₹${totals.partner_earnings}`" icon="bi-camera-reels" /></div>
            <div class="col-md-6 col-xl-3"><StatCard label="Total collected" :value="`₹${totals.revenue}`" icon="bi-graph-up-arrow" hint="Advance + final" /></div>
            <div class="col-md-6 col-xl-3"><StatCard label="Payment rows in Range" :value="totals.payments" icon="bi-cash-stack" /></div>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="table-card h-100">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Booking Report</h2></div>
                    <div class="p-4">
                        <div v-for="item in bookingStatusCounts" :key="item.status" class="d-flex justify-content-between align-items-center border rounded-4 p-3 mb-3">
                            <div>{{ headline(item.status) }}</div>
                            <strong>{{ item.count }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="table-card h-100">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Revenue by payment type</h2></div>
                    <div class="p-4">
                        <div v-for="item in paymentTypeTotals" :key="item.type" class="d-flex justify-content-between align-items-center border rounded-4 p-3 mb-3">
                            <div>{{ item.type === 'advance' ? 'Advance (platform)' : item.type === 'final' ? 'Final (partner earnings)' : headline(item.type) }}</div>
                            <strong>₹{{ item.total }}</strong>
                        </div>
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
                                <tr v-for="partner in partnerPerformance" :key="partner.id">
                                    <td>{{ partner.name }}</td>
                                    <td>{{ partner.assigned_bookings_count }}</td>
                                    <td>{{ partner.completed_bookings_count }}</td>
                                </tr>
                                <tr v-if="!partnerPerformance.length"><td colspan="3" class="text-center py-5 text-secondary">No partner stats available.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
