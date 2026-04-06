<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const page = usePage();

const appName = computed(() => page.props.appName ?? 'VM Shoot');
const status = computed(() => page.props.flash?.status);
const errors = computed(() => {
    const flashErrors = page.props.flash?.errors ?? [];
    const formErrors = page.props.errors ? Object.values(page.props.errors).flat() : [];

    return [...flashErrors, ...formErrors];
});
</script>

<template>
    <Head :title="`${title} | ${appName}`" />

    <div class="auth-shell">
        <div class="auth-hero">
            <div class="auth-brand-badge">
                <i class="bi bi-camera-fill"></i>
            </div>
            <div class="small text-uppercase text-white-50 fw-semibold">{{ appName }}</div>
            <h1 class="auth-hero-title">Premium control for bookings, partners, and delivery.</h1>
            <p class="auth-hero-copy mb-0">
                Mobile-friendly admin access for running the full photoshoot lifecycle with clarity.
            </p>
        </div>

        <div class="auth-card p-4 p-md-5">
            <div class="mb-4">
                <div class="small text-secondary text-uppercase fw-semibold">Admin Access</div>
                <h2 class="h3 mt-2 mb-2">{{ title }}</h2>
                <p class="text-secondary mb-0">{{ subtitle }}</p>
            </div>

            <div v-if="status" class="alert alert-success border-0 shadow-sm rounded-4">
                {{ status }}
            </div>

            <div v-if="errors.length" class="alert alert-danger border-0 shadow-sm rounded-4">
                <div class="fw-semibold mb-1">Please review the form</div>
                <ul class="mb-0 ps-3">
                    <li v-for="error in errors" :key="error">{{ error }}</li>
                </ul>
            </div>

            <slot />
        </div>
    </div>
</template>
