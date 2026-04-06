<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    partners: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/partners', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyPartner(partner) {
    if (window.confirm(`Delete ${partner.name}?`)) {
        router.delete(partner.delete_url);
    }
}

function toggleStatus(partner) {
    router.post(partner.status_url, {
        status: partner.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <AdminLayout title="Partners">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="h5 mb-1">Partner Management</h2>
                    <p class="text-secondary mb-0">Create, update, and monitor shoot partners.</p>
                </div>
                <Link href="/admin/partners/create" class="btn btn-primary">Add Partner</Link>
            </div>
            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" placeholder="Search name, phone, email">
                <select v-model="filters.status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="btn btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Assigned Bookings</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="partner in partners.data" :key="partner.id">
                            <td class="fw-semibold">{{ partner.name }}</td>
                            <td>{{ partner.phone }}</td>
                            <td>{{ partner.email || 'N/A' }}</td>
                            <td><StatusBadge :value="partner.status" /></td>
                            <td>{{ partner.assigned_bookings_count }}</td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="partner.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="partner.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" @click="toggleStatus(partner)">
                                    {{ partner.status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyPartner(partner)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!partners.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No partners found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <PaginationLinks :links="partners.links" />
            </div>
        </div>
    </AdminLayout>
</template>
