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
        default: 'Operations overview',
    },
});

const page = usePage();

const appName = computed(() => page.props.appName ?? 'VM Shoot');
const admin = computed(() => page.props.auth?.admin);
const currentUrl = computed(() => page.url);

const links = [
    { href: '/admin/dashboard', label: 'Dashboard', icon: 'bi-grid' },
    { href: '/admin/cities', label: 'Cities', icon: 'bi-geo-alt' },
    { href: '/admin/categories', label: 'Categories', icon: 'bi-collection' },
    { href: '/admin/plans', label: 'Plans', icon: 'bi-card-checklist' },
    { href: '/admin/reels', label: 'Reels', icon: 'bi-play-btn' },
    { href: '/admin/sliders', label: 'Sliders', icon: 'bi-images' },
    { href: '/admin/bookings', label: 'Bookings', icon: 'bi-calendar2-check' },
    { href: '/admin/booking-results', label: 'Results', icon: 'bi-images' },
    { href: '/admin/payments', label: 'Payments', icon: 'bi-credit-card-2-front' },
    { href: '/admin/users', label: 'Users', icon: 'bi-people' },
    { href: '/admin/partners', label: 'Partners', icon: 'bi-camera-reels' },
    { href: '/admin/partners/kyc/pending', label: 'Partner KYC', icon: 'bi-shield-check' },
    { href: '/admin/owners', label: 'Owners', icon: 'bi-person-badge' },
    { href: '/admin/notifications', label: 'Notifications', icon: 'bi-bell' },
    { href: '/admin/reports', label: 'Reports', icon: 'bi-bar-chart' },
    { href: '/admin/settings', label: 'Settings', icon: 'bi-gear' },
];

function isActive(href) {
    const url = currentUrl.value.split('?')[0] ?? '';

    if (href === '/admin/partners/kyc/pending') {
        return url === href;
    }

    if (href === '/admin/partners') {
        return (
            url === href ||
            (url.startsWith('/admin/partners/') && !url.startsWith('/admin/partners/kyc'))
        );
    }

    return url === href || url.startsWith(`${href}/`);
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
            <div class="topbar admin-topbar">
                <div class="d-flex align-items-center gap-2 gap-md-3 w-100 min-w-0">
                    <button
                        class="btn btn-light admin-topbar-menu-btn d-lg-none flex-shrink-0"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileSidebar"
                        aria-controls="mobileSidebar"
                        aria-label="Open menu"
                    >
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <div class="min-w-0 flex-grow-1">
                        <div class="text-secondary small d-none d-md-block text-truncate">{{ subtitle }}</div>
                        <h1 class="h5 mb-0 text-truncate d-lg-none">{{ title }}</h1>
                        <h1 class="h4 mb-0 d-none d-lg-block">{{ title }}</h1>
                    </div>

                    <!-- Desktop toolbar -->
                    <div class="d-none d-lg-flex align-items-center gap-2 gap-xl-3 flex-shrink-0">
                        <Link class="btn btn-outline-secondary btn-sm" href="/admin/notifications">
                            <i class="bi bi-bell me-1"></i>Notifications
                        </Link>
                        <div class="d-flex align-items-center gap-2" v-if="admin">
                            <div class="avatar-circle">{{ admin.name?.slice(0, 1)?.toUpperCase() }}</div>
                            <div class="d-none d-xl-block text-start" style="max-width: 12rem">
                                <div class="fw-semibold text-truncate">{{ admin.name }}</div>
                                <div class="small text-secondary text-truncate">{{ admin.email }}</div>
                            </div>
                        </div>
                        <button class="btn btn-dark btn-sm" type="button" @click="logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </div>

                    <!-- Mobile / tablet: notifications + account menu -->
                    <div class="d-flex d-lg-none align-items-center gap-1 flex-shrink-0">
                        <Link
                            class="btn btn-light admin-topbar-icon-btn"
                            href="/admin/notifications"
                            aria-label="Notifications"
                        >
                            <i class="bi bi-bell"></i>
                        </Link>
                        <div class="dropdown">
                            <button
                                class="btn btn-light admin-topbar-icon-btn"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Account menu"
                            >
                                <i class="bi bi-person-circle fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 admin-account-dropdown">
                                <li v-if="admin" class="px-3 pb-2 mb-2 border-bottom">
                                    <div class="fw-semibold text-truncate" style="max-width: 14rem">{{ admin.name }}</div>
                                    <div class="small text-secondary text-truncate" style="max-width: 14rem">{{ admin.email }}</div>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger d-flex align-items-center gap-2" type="button" @click="logout">
                                        <i class="bi bi-box-arrow-right"></i>Log out
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <FlashAlerts />
            <slot />
        </div>
    </div>

    <div
        class="offcanvas offcanvas-start mobile-sidebar d-lg-none"
        tabindex="-1"
        id="mobileSidebar"
        aria-labelledby="mobileSidebarLabel"
    >
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="brand-mark brand-mark--sm flex-shrink-0"><i class="bi bi-camera-fill"></i></div>
                <div class="min-w-0">
                    <h5 class="offcanvas-title mb-0 text-truncate" id="mobileSidebarLabel">{{ appName }}</h5>
                    <div class="mobile-sidebar-tagline">Menu</div>
                </div>
            </div>
            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"
                aria-label="Close"
            ></button>
        </div>
        <div class="offcanvas-body pt-3">
            <nav class="nav flex-column">
                <Link
                    v-for="link in links"
                    :key="`mobile-${link.href}`"
                    :href="link.href"
                    class="nav-link"
                    :class="{ active: isActive(link.href) }"
                    data-bs-dismiss="offcanvas"
                >
                    <i :class="['bi', link.icon]" />
                    <span>{{ link.label }}</span>
                </Link>
            </nav>
        </div>
    </div>
</template>
