<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
});

const filters = reactive({
    status: props.filters?.status ?? '',
    search: props.filters?.search ?? '',
});

function submitFilters() {
    router.get('/admin/booking-results', filters, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AdminLayout title="Booking Results">
        <div class="glass-card p-4 mb-4">
            <h2 class="h5 mb-3">Result Upload Tracking</h2>
            <form class="filter-grid" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" placeholder="Search booking, user, or partner">
                <select v-model="filters.status" class="form-select">
                    <option value="">All result states</option>
                    <option value="uploaded">Uploaded</option>
                    <option value="pending">Pending</option>
                </select>
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
                            <th>Partner</th>
                            <th>Result State</th>
                            <th>Files</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td>#{{ booking.id }}</td>
                            <td>{{ booking.user_name || 'Unknown user' }}</td>
                            <td>{{ booking.partner_name || 'Unassigned' }}</td>
                            <td><StatusBadge :value="booking.results_uploaded ? 'completed' : 'pending'" /></td>
                            <td>{{ booking.results_count }}</td>
                            <td class="text-end"><Link class="btn btn-sm btn-outline-primary" :href="booking.show_url">View</Link></td>
                        </tr>
                        <tr v-if="!bookings.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3"><PaginationLinks :links="bookings.links" /></div>
        </div>
    </AdminLayout>
</template>
