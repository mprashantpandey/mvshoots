<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthLayout from '../../../Layouts/AuthLayout.vue';

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: '',
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/admin/reset-password');
}
</script>

<template>
    <AuthLayout title="Reset Password" subtitle="Choose a new password to restore admin access.">
        <form @submit.prevent="submit">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control" required autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" class="form-control" required autocomplete="new-password">
            </div>
            <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">Reset Password</button>
        </form>
    </AuthLayout>
</template>
