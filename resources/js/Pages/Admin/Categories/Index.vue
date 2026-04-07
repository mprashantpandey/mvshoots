<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    categories: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/categories', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyCategory(category) {
    if (window.confirm(`Delete ${category.name}?`)) {
        router.delete(category.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Categories">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">Category Management</h2>
                    <p class="text-secondary mb-0">Create and maintain public booking categories.</p>
                </div>
                <Link href="/admin/categories/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Category
                </Link>
            </div>

            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" type="text" name="search" placeholder="Search category">
                <select v-model="filters.status" class="form-select" name="status">
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
                            <th>Image</th>
                            <th>Name</th>
                            <th>Cities</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in categories.data" :key="category.id">
                            <td>
                                <img
                                    v-if="category.image"
                                    :src="category.image"
                                    :alt="category.name"
                                    width="56"
                                    height="56"
                                    class="rounded-4 object-fit-cover"
                                >
                                <div v-else class="avatar-circle">{{ category.name.slice(0, 1).toUpperCase() }}</div>
                            </td>
                            <td class="fw-semibold">{{ category.name }}</td>
                            <td class="text-secondary">{{ category.cities?.length ? category.cities.join(', ') : 'All cities' }}</td>
                            <td class="text-secondary">{{ category.description || 'No description added.' }}</td>
                            <td><StatusBadge :value="category.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="category.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="category.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyCategory(category)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!categories.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No categories found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <PaginationLinks :links="categories.links" />
            </div>
        </div>
    </AdminLayout>
</template>
