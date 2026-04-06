<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    user: Object,
    bookings: Object,
});

function toggleStatus() {
    router.post(props.user.status_url, {
        status: props.user.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <AdminLayout title="User Details">
        <Head title="User Details" />

        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="h4 mb-1">{{ user.name }}</h2>
                    <p class="text-secondary mb-0">Customer profile and booking history</p>
                </div>
                <button class="btn btn-outline-secondary" type="button" @click="toggleStatus">
                    {{ user.status === 'active' ? 'Deactivate User' : 'Activate User' }}
                </button>
            </div>
            <div class="row g-4">
                <div class="col-md-4"><strong>Name</strong><div>{{ user.name }}</div></div>
                <div class="col-md-4"><strong>Phone</strong><div>{{ user.phone }}</div></div>
                <div class="col-md-4"><strong>Status</strong><div><StatusBadge :value="user.status" /></div></div>
                <div class="col-md-6"><strong>Email</strong><div>{{ user.email || 'No email' }}</div></div>
                <div class="col-md-6"><strong>Total Bookings</strong><div>{{ user.bookings_count }}</div></div>
            </div>
        </div>

        <div class="table-card">
            <div class="p-4 border-bottom">
                <h2 class="h5 mb-1">Booking History</h2>
                <p class="text-secondary mb-0">Recent bookings made by this customer.</p>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Plan</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Partner</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td><Link :href="booking.show_url" class="text-decoration-none fw-semibold">#{{ booking.id }}</Link></td>
                            <td>{{ booking.plan_name || 'No plan' }}</td>
                            <td>{{ booking.category_name || 'No category' }}</td>
                            <td><StatusBadge :value="booking.status" /></td>
                            <td>{{ booking.partner_name || 'Unassigned' }}</td>
                            <td>₹{{ booking.total_amount }}</td>
                        </tr>
                        <tr v-if="!bookings.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No bookings yet.</td>
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
