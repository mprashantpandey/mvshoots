<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    reel: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="Reel Details">
        <Head title="Reel Details" />

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="h4">{{ reel.title }}</h2>
                    <p class="text-secondary mb-0">{{ reel.category_name || 'Unassigned category' }}</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <StatusBadge :value="reel.status" />
                    <Link class="btn btn-outline-primary" :href="reel.edit_url">Edit</Link>
                </div>
            </div>
            <div class="row g-4 mt-1">
                <div class="col-lg-4">
                    <img v-if="reel.thumbnail" :src="reel.thumbnail" class="img-fluid rounded-5" :alt="reel.title">
                    <div v-else class="soft-empty-state">No thumbnail uploaded yet.</div>
                </div>
                <div class="col-lg-8">
                    <div class="text-secondary">Video Source</div>
                    <a v-if="reel.video_source" :href="reel.video_source" target="_blank" rel="noreferrer">{{ reel.video_url }}</a>
                    <p v-else class="mb-0">No video source added.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
