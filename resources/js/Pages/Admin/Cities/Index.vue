<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    cities: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/cities', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyCity(city) {
    if (window.confirm(`Delete ${city.name}?`)) {
        router.delete(city.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Cities">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">City Management</h2>
                    <p class="text-secondary mb-0">Manage service cities and service availability by city.</p>
                </div>
                <Link href="/admin/cities/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add City
                </Link>
            </div>

            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" type="text" placeholder="Search city">
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
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="city in cities.data" :key="city.id">
                            <td class="fw-semibold">{{ city.name }}</td>
                            <td>{{ city.sort_order }}</td>
                            <td><StatusBadge :value="city.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="city.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="city.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyCity(city)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!cities.data.length">
                            <td colspan="4" class="text-center py-5 text-secondary">No cities found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <PaginationLinks :links="cities.links" />
            </div>
        </div>
    </AdminLayout>
</template>
