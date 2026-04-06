<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    partner: Object,
});

function headline(value) {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function toggleStatus() {
    router.post(props.partner.status_url, {
        status: props.partner.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <AdminLayout title="Partner Details">
        <Head title="Partner Details" />
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="h4 mb-1">{{ partner.name }}</h2>
                    <p class="text-secondary mb-0">Partner profile, assignments, and uploads</p>
                </div>
                <button class="btn btn-outline-secondary" type="button" @click="toggleStatus">
                    {{ partner.status === 'active' ? 'Deactivate Partner' : 'Activate Partner' }}
                </button>
            </div>
            <div class="row g-4">
                <div class="col-md-3"><strong>Name</strong><div>{{ partner.name }}</div></div>
                <div class="col-md-3"><strong>Phone</strong><div>{{ partner.phone }}</div></div>
                <div class="col-md-3"><strong>Email</strong><div>{{ partner.email || 'N/A' }}</div></div>
                <div class="col-md-3"><strong>Status</strong><div><StatusBadge :value="partner.status" /></div></div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="table-card">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Assigned Bookings</h2></div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>#</th><th>User</th><th>Plan</th><th>Status</th></tr></thead>
                            <tbody>
                                <tr v-for="booking in partner.assigned_bookings" :key="booking.id">
                                    <td><Link :href="booking.show_url" class="text-decoration-none fw-semibold">#{{ booking.id }}</Link></td>
                                    <td>{{ booking.user_name || 'Unknown user' }}</td>
                                    <td>{{ booking.plan_name || 'No plan' }}</td>
                                    <td><StatusBadge :value="booking.status" /></td>
                                </tr>
                                <tr v-if="!partner.assigned_bookings?.length"><td colspan="4" class="text-center py-5 text-secondary">No assigned bookings.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-card">
                    <div class="p-4 border-bottom"><h2 class="h5 mb-0">Uploaded Results</h2></div>
                    <div class="p-4">
                        <div v-for="result in partner.booking_results" :key="result.id" class="border rounded-4 p-3 mb-3">
                            <div class="fw-semibold">{{ headline(result.file_type) }}</div>
                            <div class="small text-secondary mb-2">{{ result.notes || 'No notes' }}</div>
                            <a :href="result.file_url" target="_blank" rel="noreferrer" class="small text-decoration-none">View file</a>
                        </div>
                        <div v-if="!partner.booking_results?.length" class="text-secondary">No result uploads yet.</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
