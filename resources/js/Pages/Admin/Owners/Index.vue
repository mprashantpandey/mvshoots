<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    owners: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/owners', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyOwner(owner) {
    if (window.confirm(`Delete ${owner.name}?`)) {
        router.delete(owner.delete_url);
    }
}

function toggleStatus(owner) {
    router.post(owner.status_url, {
        status: owner.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <AdminLayout title="Owners">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="h5 mb-1">Owner Management</h2>
                    <p class="text-secondary mb-0">Manage secure owner accounts and access.</p>
                </div>
                <Link href="/admin/owners/create" class="btn btn-primary">Add Owner</Link>
            </div>
            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" placeholder="Search name or email">
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
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="owner in owners.data" :key="owner.id">
                            <td class="fw-semibold">{{ owner.name }}</td>
                            <td>{{ owner.email }}</td>
                            <td><StatusBadge :value="owner.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="owner.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="owner.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" @click="toggleStatus(owner)">
                                    {{ owner.status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyOwner(owner)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!owners.data.length">
                            <td colspan="4" class="text-center py-5 text-secondary">No owners found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <PaginationLinks :links="owners.links" />
            </div>
        </div>
    </AdminLayout>
</template>
