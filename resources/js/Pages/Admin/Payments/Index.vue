<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatCard from '../../../Components/Admin/StatCard.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    payments: Object,
    filters: Object,
    summary: Object,
});

const filters = reactive({
    booking_id: props.filters?.booking_id ?? '',
    user: props.filters?.user ?? '',
    payment_type: props.filters?.payment_type ?? '',
    payment_status: props.filters?.payment_status ?? '',
    date: props.filters?.date ?? '',
});

function submitFilters() {
    router.get('/admin/payments', filters, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AdminLayout title="Payments">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <StatCard label="Advance Collected" :value="`₹${summary.advance_paid}`" icon="bi-wallet2" />
            </div>
            <div class="col-md-4">
                <StatCard label="Final Collected" :value="`₹${summary.final_paid}`" icon="bi-cash-coin" />
            </div>
            <div class="col-md-4">
                <StatCard label="Pending Payments" :value="summary.pending" icon="bi-hourglass-split" />
            </div>
        </div>

        <div class="glass-card p-4 mb-4">
            <h2 class="h5 mb-3">Payment Tracking</h2>

            <form class="filter-grid" @submit.prevent="submitFilters">
                <input v-model="filters.booking_id" class="form-control" placeholder="Booking ID">
                <input v-model="filters.user" class="form-control" placeholder="User">
                <select v-model="filters.payment_type" class="form-select">
                    <option value="">All types</option>
                    <option value="advance">Advance</option>
                    <option value="final">Final</option>
                </select>
                <select v-model="filters.payment_status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                </select>
                <input v-model="filters.date" class="form-control" type="date">
                <button class="btn btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Paid At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in payments.data" :key="payment.id">
                            <td>#{{ payment.booking_id }}</td>
                            <td>{{ payment.user_name || 'Unknown user' }}</td>
                            <td><StatusBadge :value="payment.payment_type" /></td>
                            <td><StatusBadge :value="payment.payment_status" /></td>
                            <td>₹{{ payment.amount }}</td>
                            <td>{{ payment.paid_at || 'Pending' }}</td>
                            <td class="text-end"><Link class="btn btn-sm btn-outline-primary" :href="payment.show_url">Details</Link></td>
                        </tr>
                        <tr v-if="!payments.data.length">
                            <td colspan="7" class="text-center py-5 text-secondary">No payments found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <PaginationLinks :links="payments.links" />
            </div>
        </div>
    </AdminLayout>
</template>
