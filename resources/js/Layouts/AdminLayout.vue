<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import FlashAlerts from '../Components/Admin/FlashAlerts.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Admin Panel',
    },
    subtitle: {
        type: String,
        default: 'Premium responsive dashboard',
    },
});

const page = usePage();

const appName = computed(() => page.props.appName ?? 'VM Shoot');
const admin = computed(() => page.props.auth?.admin);
const currentUrl = computed(() => page.url);

const links = [
    { href: '/admin/dashboard', label: 'Dashboard', icon: 'bi-grid' },
    { href: '/admin/categories', label: 'Categories', icon: 'bi-collection' },
    { href: '/admin/plans', label: 'Plans', icon: 'bi-card-checklist' },
    { href: '/admin/reels', label: 'Reels', icon: 'bi-play-btn' },
    { href: '/admin/sliders', label: 'Sliders', icon: 'bi-images' },
    { href: '/admin/bookings', label: 'Bookings', icon: 'bi-calendar2-check' },
    { href: '/admin/booking-results', label: 'Results', icon: 'bi-images' },
    { href: '/admin/payments', label: 'Payments', icon: 'bi-credit-card-2-front' },
    { href: '/admin/users', label: 'Users', icon: 'bi-people' },
    { href: '/admin/partners', label: 'Partners', icon: 'bi-camera-reels' },
    { href: '/admin/owners', label: 'Owners', icon: 'bi-person-badge' },
    { href: '/admin/notifications', label: 'Notifications', icon: 'bi-bell' },
    { href: '/admin/reports', label: 'Reports', icon: 'bi-bar-chart' },
    { href: '/admin/settings', label: 'Settings', icon: 'bi-gear' },
];

function isActive(href) {
    return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
}

function logout() {
    router.post('/admin/logout');
}
</script>

<template>
    <Head :title="`${title} | ${appName}`" />

    <div class="app-shell">
        <aside class="sidebar d-none d-lg-block">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="brand-mark"><i class="bi bi-camera-fill"></i></div>
                <div>
                    <div class="fw-bold">{{ appName }}</div>
                    <div class="small text-white-50">Admin Console</div>
                </div>
            </div>

            <div class="soft-section-title">Main Menu</div>
            <nav class="nav flex-column">
                <Link
                    v-for="link in links"
                    :key="link.href"
                    :href="link.href"
                    class="nav-link"
                    :class="{ active: isActive(link.href) }"
                >
                    <i :class="['bi', link.icon]" />
                    <span>{{ link.label }}</span>
                </Link>
            </nav>
        </aside>

        <div class="content-area">
            <div class="topbar d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div>
                        <div class="text-secondary small">{{ subtitle }}</div>
                        <h1 class="h4 mb-0">{{ title }}</h1>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <Link class="btn btn-outline-secondary" href="/admin/notifications">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </Link>
                    <div class="d-flex align-items-center gap-2" v-if="admin">
                        <div class="avatar-circle">{{ admin.name?.slice(0, 1)?.toUpperCase() }}</div>
                        <div>
                            <div class="fw-semibold">{{ admin.name }}</div>
                            <div class="small text-secondary">{{ admin.email }}</div>
                        </div>
                    </div>
                    <button class="btn btn-dark" type="button" @click="logout">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </div>
            </div>

            <FlashAlerts />
            <slot />
        </div>
    </div>

    <div class="offcanvas offcanvas-start mobile-sidebar d-lg-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header border-bottom border-secondary-subtle">
            <h5 class="offcanvas-title">{{ appName }} Admin</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="nav flex-column">
                <Link
                    v-for="link in links"
                    :key="`mobile-${link.href}`"
                    :href="link.href"
                    class="nav-link"
                    :class="{ active: isActive(link.href) }"
                >
                    <i :class="['bi', link.icon]" />
                    <span>{{ link.label }}</span>
                </Link>
            </nav>
        </div>
    </div>
</template>
