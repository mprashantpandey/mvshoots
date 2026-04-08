<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineProps({
    admins: {
        type: Array,
        default: () => [],
    },
});

function destroyRow(row) {
    if (!row.delete_url) {
        return;
    }
    if (window.confirm(`Remove administrator ${row.name}? They will no longer be able to sign in.`)) {
        router.delete(row.delete_url);
    }
}
</script>

<template>
    <AdminLayout title="Staff" subtitle="Main admin and city administrators">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <h2 class="h5 mb-1">Administrators</h2>
                    <p class="text-secondary mb-0">
                        One main platform account; create city admins who sign in at the same login page with access limited to their city.
                    </p>
                </div>
                <Link href="/admin/staff/create" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Add city admin
                </Link>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Scope</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in admins" :key="row.id">
                            <td class="fw-semibold">{{ row.name }}</td>
                            <td>{{ row.email }}</td>
                            <td>
                                <span v-if="row.is_main" class="badge text-bg-primary">Main administrator</span>
                                <span v-else class="badge text-bg-secondary">City · {{ row.city_name || '—' }}</span>
                            </td>
                            <td class="text-end">
                                <Link v-if="row.edit_url" class="btn btn-sm btn-outline-primary" :href="row.edit_url">Edit</Link>
                                <button
                                    v-if="row.delete_url"
                                    type="button"
                                    class="btn btn-sm btn-outline-danger ms-2"
                                    @click="destroyRow(row)"
                                >
                                    Remove
                                </button>
                                <span v-if="!row.edit_url && !row.delete_url" class="text-secondary small">—</span>
                            </td>
                        </tr>
                        <tr v-if="!admins.length">
                            <td colspan="4" class="text-center py-4 text-secondary">No administrators found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
