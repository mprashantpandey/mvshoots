<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/users', filters, {
        preserveState: true,
        replace: true,
    });
}

function toggleStatus(user) {
    const nextStatus = user.status === 'active' ? 'inactive' : 'active';

    router.post(user.status_url, {
        status: nextStatus,
    });
}
</script>

<template>
    <AdminLayout title="Users">
        <div class="glass-card p-4 mb-4">
            <h2 class="h5 mb-3">User Management</h2>

            <form class="filter-grid" @submit.prevent="submitFilters">
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
                            <th>Bookings</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="fw-semibold">{{ user.name }}</td>
                            <td>{{ user.phone }}</td>
                            <td>{{ user.email || 'N/A' }}</td>
                            <td><StatusBadge :value="user.status" /></td>
                            <td>{{ user.bookings_count }}</td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-outline-primary" :href="user.show_url">View</Link>
                                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" @click="toggleStatus(user)">
                                    {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!users.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <PaginationLinks :links="users.links" />
            </div>
        </div>
    </AdminLayout>
</template>
