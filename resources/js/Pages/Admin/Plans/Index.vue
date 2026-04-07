<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import PaginationLinks from '../../../Components/Admin/PaginationLinks.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    plans: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
});

function submitFilters() {
    router.get('/admin/plans', filters, {
        preserveState: true,
        replace: true,
    });
}

function destroyPlan(plan) {
    if (window.confirm(`Delete ${plan.title}?`)) {
        router.delete(plan.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Plans">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">Plan Management</h2>
                    <p class="text-secondary mb-0">Build premium packages for each shoot category.</p>
                </div>
                <Link href="/admin/plans/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Plan
                </Link>
            </div>

            <form class="filter-grid mt-4" @submit.prevent="submitFilters">
                <input v-model="filters.search" class="form-control" type="text" name="search" placeholder="Search plan">
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
                            <th>Title</th>
                            <th>Category</th>
                            <th>Cities</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="plan in plans.data" :key="plan.id">
                            <td class="fw-semibold">{{ plan.title }}</td>
                            <td class="text-secondary">{{ plan.category_name || 'Unassigned' }}</td>
                            <td class="text-secondary">{{ plan.cities?.length ? plan.cities.join(', ') : 'All cities' }}</td>
                            <td>₹{{ plan.price }}</td>
                            <td>{{ plan.duration }}</td>
                            <td><StatusBadge :value="plan.status" /></td>
                            <td class="text-end">
                                <Link class="btn btn-sm btn-light" :href="plan.show_url">View</Link>
                                <Link class="btn btn-sm btn-outline-primary ms-2" :href="plan.edit_url">Edit</Link>
                                <button class="btn btn-sm btn-outline-danger ms-2" type="button" @click="destroyPlan(plan)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!plans.data.length">
                            <td colspan="7" class="text-center py-5 text-secondary">No plans found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <PaginationLinks :links="plans.links" />
            </div>
        </div>
    </AdminLayout>
</template>
