<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    city: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="City Details">
        <Head title="City Details" />

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="h4">{{ city.name }}</h2>
                    <div class="mt-2"><StatusBadge :value="city.status" /></div>
                </div>
                <Link class="btn btn-outline-primary" :href="city.edit_url">Edit</Link>
            </div>
            <div class="row g-4 mt-1">
                <div class="col-md-3">
                    <div class="text-secondary">Sort Order</div>
                    <div class="fw-semibold">{{ city.sort_order }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-secondary">Users</div>
                    <div class="fw-semibold">{{ city.users_count }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-secondary">Bookings</div>
                    <div class="fw-semibold">{{ city.bookings_count }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-secondary">Plans</div>
                    <div class="fw-semibold">{{ city.plans_count }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary">Categories Available</div>
                    <div class="fw-semibold">{{ city.categories_count }}</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
