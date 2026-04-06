<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../../Layouts/AuthLayout.vue';

const form = useForm({
    email: '',
});

function submit() {
    form.post('/admin/forgot-password');
}
</script>

<template>
    <AuthLayout title="Forgot Password" subtitle="Enter the admin email address to send a reset link.">
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required autocomplete="email">
            </div>
            <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Send Reset Link</button>
        </form>
        <Link class="btn btn-link w-100 mt-3 text-decoration-none" href="/admin/login">Back to login</Link>
    </AuthLayout>
</template>
