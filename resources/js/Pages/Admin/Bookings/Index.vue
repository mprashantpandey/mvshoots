<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    bookings: Object,
    partners: Array,
    categories: Array,
    plans: Array,
    filters: Object,
    statusOptions: Array,
});

const filters = reactive({
    booking_id: props.filters?.booking_id ?? '',
    user: props.filters?.user ?? '',
    partner_id: props.filters?.partner_id ?? '',
    category_id: props.filters?.category_id ?? '',
    plan_id: props.filters?.plan_id ?? '',
    date: props.filters?.date ?? '',
    payment_status: props.filters?.payment_status ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/bookings', filters, {
        preserveState: true,
        replace: true,
    });
}

function headline(value) {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>
    <AdminLayout title="Bookings">
        <div class="glass-card p-4 mb-4">
            <h2 class="h5 mb-3">Booking Management</h2>

            <form class="filter-grid" @submit.prevent="submitFilters">
                <input v-model="filters.booking_id" class="form-control" placeholder="Booking ID">
                <input v-model="filters.user" class="form-control" placeholder="User">
                <select v-model="filters.partner_id" class="form-select">
                    <option value="">All partners</option>
                    <option v-for="partner in partners" :key="partner.id" :value="partner.id">{{ partner.name }}</option>
                </select>
                <select v-model="filters.category_id" class="form-select">
                    <option value="">All categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
                <select v-model="filters.plan_id" class="form-select">
                    <option value="">All plans</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.title }}</option>
                </select>
                <input v-model="filters.date" class="form-control" type="date">
                <select v-model="filters.payment_status" class="form-select">
                    <option value="">All payment states</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
                <select v-model="filters.status" class="form-select">
                    <option value="">All booking statuses</option>
                    <option v-for="status in statusOptions" :key="status" :value="status">{{ headline(status) }}</option>
                </select>
                <button class="btn btn-outline-primary" type="submit">Apply Filters</button>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Category</th>
                            <th>Plan</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Partner</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td class="fw-semibold">#{{ booking.id }}</td>
                            <td>{{ booking.user_name || 'Unknown user' }}</td>
                            <td>{{ booking.category_name || 'Unassigned' }}</td>
                            <td>{{ booking.plan_name || 'Unassigned' }}</td>
                            <td>
                                {{ booking.booking_date || 'TBD' }}<br>
                                <span class="text-secondary small">{{ booking.booking_time || 'TBD' }}</span>
                            </td>
                            <td><StatusBadge :value="booking.status" /></td>
                            <td>{{ booking.partner_name || 'Unassigned' }}</td>
                            <td class="text-end"><Link class="btn btn-sm btn-outline-primary" :href="booking.show_url">Details</Link></td>
                        </tr>
                        <tr v-if="!bookings.data.length">
                            <td colspan="8" class="text-center py-5 text-secondary">No bookings found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <PaginationLinks :links="bookings.links" />
            </div>
        </div>
    </AdminLayout>
</template>
