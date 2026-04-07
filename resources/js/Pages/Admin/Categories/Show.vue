<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="Category Details">
        <Head title="Category Details" />

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="h4">{{ category.name }}</h2>
                    <div class="mt-2"><StatusBadge :value="category.status" /></div>
                </div>
                <Link class="btn btn-outline-primary" :href="category.edit_url">Edit</Link>
            </div>
            <div class="row g-4 mt-1">
                <div class="col-lg-4">
                    <img v-if="category.image" :src="category.image" class="img-fluid rounded-5" :alt="category.name">
                </div>
                <div class="col-lg-8">
                    <div class="text-secondary">Description</div>
                    <p class="mb-0">{{ category.description || 'No description added.' }}</p>
                    <div class="text-secondary mt-4">Cities</div>
                    <p class="mb-0">
                        {{ category.cities?.length ? category.cities.join(', ') : 'All cities' }}
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
