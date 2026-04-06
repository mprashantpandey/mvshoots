<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

defineProps({
    booking: Object,
});

function headline(value) {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>
    <AdminLayout title="Booking Result Details">
        <Head title="Booking Result Details" />
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="h4 mb-1">Booking #{{ booking.id }}</h2>
                    <p class="text-secondary mb-0">{{ booking.user_name || 'Unknown user' }} • {{ booking.partner_name || 'No partner assigned' }}</p>
                </div>
                <StatusBadge :value="booking.results_uploaded ? 'completed' : 'pending'" />
            </div>
        </div>
        <div class="row g-4">
            <div v-for="result in booking.results" :key="result.id" class="col-md-6 col-xl-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>{{ headline(result.file_type) }}</strong>
                        <span class="small text-secondary">{{ result.partner_name || 'Partner' }}</span>
                    </div>
                    <p class="text-secondary">{{ result.notes || 'No notes added.' }}</p>
                    <a class="btn btn-outline-primary btn-sm" :href="result.file_url" target="_blank" rel="noreferrer">Open File</a>
                </div>
            </div>
            <div v-if="!booking.results?.length" class="col-12">
                <div class="glass-card p-5 text-center text-secondary">No results uploaded for this booking yet.</div>
            </div>
        </div>
    </AdminLayout>
</template>
