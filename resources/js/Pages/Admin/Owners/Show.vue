<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';

const props = defineProps({
    owner: Object,
});

function toggleStatus() {
    router.post(props.owner.status_url, {
        status: props.owner.status === 'active' ? 'inactive' : 'active',
    });
}
</script>

<template>
    <AdminLayout title="Owner Details">
        <Head title="Owner Details" />
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="h4 mb-1">{{ owner.name }}</h2>
                    <p class="text-secondary mb-0">Owner access and account details</p>
                </div>
                <button class="btn btn-outline-secondary" type="button" @click="toggleStatus">
                    {{ owner.status === 'active' ? 'Deactivate Owner' : 'Activate Owner' }}
                </button>
            </div>
            <div class="row g-4">
                <div class="col-md-4"><strong>Name</strong><div>{{ owner.name }}</div></div>
                <div class="col-md-4"><strong>Email</strong><div>{{ owner.email }}</div></div>
                <div class="col-md-4"><strong>Status</strong><div><StatusBadge :value="owner.status" /></div></div>
            </div>
        </div>
    </AdminLayout>
</template>
