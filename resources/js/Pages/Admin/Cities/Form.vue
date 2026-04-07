<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    city: {
        type: Object,
        default: null,
    },
    submitUrl: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        required: true,
    },
});

const form = useForm({
    name: props.city?.name ?? '',
    status: props.city?.status ?? 'active',
    sort_order: props.city?.sort_order ?? 0,
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.city ? 'Edit City' : 'Create City'));

function submit() {
    form.post(props.submitUrl);
}
</script>

<template>
    <AdminLayout :title="title">
        <Head :title="title" />

        <div class="glass-card p-4">
            <h2 class="h5 mb-4">{{ props.city ? 'Update city' : 'Add new city' }}</h2>

            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">City Name</label>
                        <input v-model="form.name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input v-model="form.sort_order" class="form-control" type="number" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save City</button>
                    <Link class="btn btn-outline-secondary" href="/admin/cities">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
