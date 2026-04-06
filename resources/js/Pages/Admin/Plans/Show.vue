<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    plan: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="Plan Details">
        <Head title="Plan Details" />

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="h4">{{ plan.title }}</h2>
                    <p class="text-secondary mb-0">{{ plan.category_name || 'Unassigned category' }}</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <StatusBadge :value="plan.status" />
                    <Link class="btn btn-outline-primary" :href="plan.edit_url">Edit</Link>
                </div>
            </div>
            <div class="row g-4 mt-1">
                <div class="col-md-4">
                    <div class="text-secondary">Price</div>
                    <div class="fw-semibold">₹{{ plan.price }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary">Duration</div>
                    <div class="fw-semibold">{{ plan.duration }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary">Category</div>
                    <div class="fw-semibold">{{ plan.category_name || 'Unassigned' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-secondary">Description</div>
                    <p class="mb-0">{{ plan.description || 'No description added.' }}</p>
                </div>
                <div class="col-12">
                    <div class="text-secondary">Inclusions</div>
                    <ul v-if="plan.inclusions?.length" class="mb-0 ps-3">
                        <li v-for="inclusion in plan.inclusions" :key="inclusion">{{ inclusion }}</li>
                    </ul>
                    <p v-else class="mb-0">No inclusions added.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
