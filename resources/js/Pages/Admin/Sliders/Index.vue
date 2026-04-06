<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    sliders: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    app_target: props.filters?.app_target ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/sliders', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroySlider(slider) {
    if (window.confirm(`Delete slider${slider.title ? ` "${slider.title}"` : ''}?`)) {
        router.delete(slider.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Sliders">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">Home Sliders</h2>
                    <p class="text-secondary mb-0">Manage banner images for the user and partner apps.</p>
                </div>
                <Link href="/admin/sliders/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Slider
                </Link>
            </div>

            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" type="text" placeholder="Search slider">
                <select v-model="filters.app_target" class="form-select">
                    <option value="">All apps</option>
                    <option value="user">User</option>
                    <option value="partner">Partner</option>
                    <option value="both">Both</option>
                </select>
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
                            <th>Image</th>
                            <th>Title</th>
                            <th>Target</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="slider in sliders.data" :key="slider.id">
                            <td>
                                <img
                                    v-if="slider.image"
                                    :src="slider.image"
                                    :alt="slider.title || 'Slider image'"
                                    width="96"
                                    height="64"
                                    class="rounded-4 object-fit-cover"
                                >
                                <div v-else class="avatar-circle">S</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ slider.title || 'Untitled slider' }}</div>
                                <div class="small text-secondary">{{ slider.subtitle || 'No subtitle' }}</div>
                            </td>
                            <td class="text-capitalize">{{ slider.app_target }}</td>
                            <td>{{ slider.sort_order }}</td>
                            <td><StatusBadge :value="slider.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-outline-primary" :href="slider.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroySlider(slider)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!sliders.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No sliders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <PaginationLinks :links="sliders.links" />
            </div>
        </div>
    </AdminLayout>
</template>
