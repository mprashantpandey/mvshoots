<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    reels: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/reels', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyReel(reel) {
    if (window.confirm(`Delete ${reel.title}?`)) {
        router.delete(reel.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Reels">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">Reels Management</h2>
                    <p class="text-secondary mb-0">Manage public showcase videos and thumbnails from one place.</p>
                </div>
                <Link href="/admin/reels/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Reel
                </Link>
            </div>

            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" type="text" name="search" placeholder="Search reel">
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
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="reel in reels.data" :key="reel.id">
                            <td>
                                <img
                                    v-if="reel.thumbnail"
                                    :src="reel.thumbnail"
                                    :alt="reel.title"
                                    width="72"
                                    height="72"
                                    class="rounded-4 object-fit-cover"
                                >
                                <div v-else class="avatar-circle">{{ reel.title.slice(0, 1).toUpperCase() }}</div>
                            </td>
                            <td class="fw-semibold">{{ reel.title }}</td>
                            <td class="text-secondary">{{ reel.category_name || 'Unassigned' }}</td>
                            <td class="text-secondary text-truncate" style="max-width: 220px;">{{ reel.video_url || 'No source' }}</td>
                            <td><StatusBadge :value="reel.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="reel.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="reel.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyReel(reel)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!reels.data.length">
                            <td colspan="6" class="text-center py-5 text-secondary">No reels found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <PaginationLinks :links="reels.links" />
            </div>
        </div>
    </AdminLayout>
</template>
