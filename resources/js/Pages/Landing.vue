<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const appName = computed(() => page.props.appName ?? 'VM Shoot');
</script>

<template>
    <Head :title="`${appName} | Premium Photoshoot Booking Platform`" />

    <div class="landing-shell">
        <header class="landing-topbar">
            <div class="landing-brand">
                <div class="landing-brand-mark">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div>
                    <div class="fw-bold">{{ appName }}</div>
                    <div class="small text-secondary">Photoshoot Booking Platform</div>
                </div>
            </div>
            <a class="btn btn-primary" href="/admin/login">Admin Login</a>
        </header>

        <section class="hero-panel">
            <div class="hero-copy">
                <div class="hero-pill">Photoshoot Booking Platform</div>
                <h1 class="hero-title">Run your booking operations from one clean admin panel.</h1>
                <p class="hero-subtitle">
                    Manage categories, plans, reels, bookings, partners, payments, notifications, and reports in one place.
                </p>
                <a class="btn btn-primary btn-lg px-4" href="/admin/login">Open Admin Panel</a>
            </div>
            <div class="hero-stats-grid">
                <div class="landing-soft-card">
                    <div class="small text-secondary text-uppercase fw-semibold">Admin</div>
                    <div class="display-6 fw-bold mt-2">Ready</div>
                    <div class="text-secondary">Operations dashboard with end-to-end booking control.</div>
                </div>
                <div class="landing-soft-card">
                    <div class="small text-secondary text-uppercase fw-semibold">Payments</div>
                    <div class="display-6 fw-bold mt-2">20 / 80</div>
                    <div class="text-secondary">Advance and final payment tracking built in.</div>
                </div>
                <div class="landing-soft-card landing-soft-card-accent">
                    <div class="small text-white-50 text-uppercase fw-semibold">Access</div>
                    <div class="display-6 fw-bold mt-2 text-white">Secure</div>
                    <div class="text-white-50">Admin login, password reset, and protected workflows are in place.</div>
                </div>
            </div>
        </section>
    </div>
</template>
