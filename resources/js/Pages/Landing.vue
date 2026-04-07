<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const appName = computed(() => page.props.appName ?? 'VM Shoot');
</script>

<template>
    <Head :title="`${appName} | Book your photoshoot`" />

    <div class="landing-shell landing-shell--customer">
        <header class="landing-topbar landing-topbar--customer">
            <div class="landing-brand">
                <div class="landing-brand-mark">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div class="min-w-0">
                    <div class="fw-bold text-truncate">{{ appName }}</div>
                    <div class="small text-secondary d-none d-sm-block">Photoshoots, your way</div>
                </div>
            </div>
            <div class="landing-topbar-actions">
                <a class="btn btn-outline-secondary btn-sm px-3" href="/admin/login">
                    <i class="bi bi-shield-lock d-sm-inline d-none me-1"></i>
                    <span class="d-none d-sm-inline">Staff login</span>
                    <span class="d-sm-none">Staff</span>
                </a>
            </div>
        </header>

        <section class="hero-panel hero-panel--customer">
            <div class="hero-copy">
                <div class="hero-pill">For customers</div>
                <h1 class="hero-title hero-title--customer">
                    Book a shoot. Relax. Get beautiful memories back.
                </h1>
                <p class="hero-subtitle">
                    Pick a category and plan that fits your day, pay securely in stages, and track your booking
                    until your photos and videos are ready—supported by verified partners in your city.
                </p>
                <div class="hero-cta-row">
                    <span class="landing-inline-note">
                        <i class="bi bi-phone me-2 text-primary"></i>
                        Customers use the <strong>VM Shoot</strong> app to book—fast, clear, and mobile-first.
                    </span>
                </div>
            </div>
            <div class="hero-stats-grid hero-journey-grid">
                <div class="landing-soft-card landing-journey-card">
                    <div class="landing-journey-step">1</div>
                    <div class="fw-semibold mb-1">Choose &amp; schedule</div>
                    <div class="small text-secondary">Browse plans, select a date and time, and share your location.</div>
                </div>
                <div class="landing-soft-card landing-journey-card">
                    <div class="landing-journey-step">2</div>
                    <div class="fw-semibold mb-1">Pay with confidence</div>
                    <div class="small text-secondary">Advance and final payments are tracked so you always know what’s paid.</div>
                </div>
                <div class="landing-soft-card landing-soft-card-accent landing-journey-card">
                    <div class="landing-journey-step landing-journey-step--light">3</div>
                    <div class="fw-semibold mb-1 text-white">Your deliverables</div>
                    <div class="small text-white-50">When your shoot is complete, access your results and enjoy the outcome.</div>
                </div>
            </div>
        </section>

        <section class="landing-section landing-value-strip">
            <div class="text-secondary small text-uppercase fw-bold letter-space mb-3">Why families &amp; creators choose us</div>
            <div class="landing-card-grid landing-value-grid">
                <div class="landing-feature-card">
                    <div class="landing-feature-icon"><i class="bi bi-people"></i></div>
                    <div class="fw-semibold mb-1">Real partners</div>
                    <div class="small text-secondary mb-0">Shoots are handled by partners we onboard and verify—not random freelancers.</div>
                </div>
                <div class="landing-feature-card">
                    <div class="landing-feature-icon"><i class="bi bi-credit-card-2-front"></i></div>
                    <div class="fw-semibold mb-1">Transparent payments</div>
                    <div class="small text-secondary mb-0">See advance and final amounts upfront; pay when milestones are due.</div>
                </div>
                <div class="landing-feature-card">
                    <div class="landing-feature-icon"><i class="bi bi-images"></i></div>
                    <div class="fw-semibold mb-1">Results you can open anywhere</div>
                    <div class="small text-secondary mb-0">Photos and videos delivered in one place when your booking is complete.</div>
                </div>
            </div>
        </section>

        <section class="landing-cta landing-cta--staff">
            <div>
                <div class="fw-semibold mb-1">Running shoots or operations?</div>
                <div class="small text-secondary mb-0">
                    Admin tools stay behind a secure login—manage cities, plans, bookings, partners, and payouts in one console.
                </div>
            </div>
            <a class="btn btn-primary flex-shrink-0" href="/admin/login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Open admin
            </a>
        </section>
    </div>
</template>
