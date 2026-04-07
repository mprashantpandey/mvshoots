<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    partner: {
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
    cities: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.partner?.name ?? '',
    phone: props.partner?.phone ?? '',
    email: props.partner?.email ?? '',
    status: props.partner?.status ?? 'active',
    city_id: props.partner?.city_id ?? '',
    service_city_ids: props.partner?.service_city_ids ?? [],
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
                    <div class="col-md-6">
                        <label class="form-label">Primary city</label>
                        <select v-model="form.city_id" class="form-select">
                            <option value="">None</option>
                            <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                        </select>
                        <div class="form-text">Used when no service cities are set (legacy fallback).</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Service cities</label>
                        <select v-model="form.service_city_ids" class="form-select" multiple size="6">
                            <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                        </select>
                        <div class="form-text">Partners only receive assignments in these cities. Leave empty to use the primary city only.</div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save Partner</button>
                    <Link class="btn btn-outline-secondary" href="/admin/partners">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
