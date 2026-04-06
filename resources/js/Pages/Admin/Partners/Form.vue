<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    partner: {
        type: Object,
        default: null,
    },
    submitUrl: String,
    method: String,
});

const form = useForm({
    name: props.partner?.name ?? '',
    phone: props.partner?.phone ?? '',
    email: props.partner?.email ?? '',
    status: props.partner?.status ?? 'active',
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.partner ? 'Edit Partner' : 'Create Partner'));

function submit() {
    form.post(props.submitUrl);
}
</script>

<template>
    <AdminLayout :title="title">
        <Head :title="title" />
        <div class="glass-card p-4">
            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label">Name</label><input v-model="form.name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input v-model="form.phone" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input v-model="form.email" class="form-control" type="email"></div>
                    <div class="col-md-6"><label class="form-label">Status</label><select v-model="form.status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Partner</button>
                    <Link class="btn btn-outline-secondary" href="/admin/partners">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
