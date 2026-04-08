<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

const props = defineProps({
    adminUser: {
        type: Object,
        default: null,
    },
    cities: {
        type: Array,
        default: () => [],
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
    name: props.adminUser?.name ?? '',
    email: props.adminUser?.email ?? '',
    password: '',
    password_confirmation: '',
    city_id: props.adminUser?.city_id ?? '',
    _method: props.method === 'put' ? 'put' : 'post',
});

const title = computed(() => (props.adminUser ? 'Edit city admin' : 'Add city admin'));

function submit() {
    form.post(props.submitUrl);
}
</script>

<template>
    <AdminLayout :title="title" subtitle="City admins use the same sign-in page as the main account">
        <Head :title="title" />
        <div class="glass-card p-4">
            <form @submit.prevent="submit">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input v-model="form.name" class="form-control" required autocomplete="name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input v-model="form.email" class="form-control" type="email" required autocomplete="email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select v-model="form.city_id" class="form-select" required>
                            <option disabled value="">Select city</option>
                            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <div class="form-text">This account only sees bookings, partners, and data for this city.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ adminUser ? 'New password (optional)' : 'Password' }}</label>
                        <input v-model="form.password" class="form-control" type="password" autocomplete="new-password" :required="!adminUser">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm password</label>
                        <input v-model="form.password_confirmation" class="form-control" type="password" autocomplete="new-password" :required="!adminUser">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Save</button>
                    <Link class="btn btn-outline-secondary" href="/admin/staff">Cancel</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
