<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../../Layouts/AuthLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/admin/login');
}
</script>

<template>
    <AuthLayout title="Admin Login" subtitle="Email and password access for the operations dashboard.">
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required autocomplete="email">
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Password</label>
                    <Link href="/admin/forgot-password" class="small text-decoration-none">Forgot password?</Link>
                </div>
                <input v-model="form.password" type="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-check mb-4">
                <input id="remember" v-model="form.remember" class="form-check-input" type="checkbox">
                <label class="form-check-label" for="remember">Keep me signed in</label>
            </div>
            <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Sign In</button>
        </form>
    </AuthLayout>
</template>
