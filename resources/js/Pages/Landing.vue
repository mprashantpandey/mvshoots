<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appName = computed(() => page.props.appName ?? 'VM Shoot');

const steps = [
    {
        n: '01',
        title: 'Browse & book',
        text: 'Plans, dates, and locations—picked in minutes from your phone.',
    },
    {
        n: '02',
        title: 'Pay in stages',
        text: 'Advance and final payments stay clear; nothing hidden in fine print.',
    },
    {
        n: '03',
        title: 'Get your memories',
        text: 'When the shoot wraps, your deliverables show up in one place.',
    },
];

const pillars = [
    {
        icon: 'bi-shield-check',
        title: 'Verified partners',
        text: 'Onboarded teams—not anonymous freelancers. Identity checks before assignment.',
    },
    {
        icon: 'bi-wallet2',
        title: 'Transparent money flow',
        text: 'You always know what is due, what is paid, and what is next.',
    },
    {
        icon: 'bi-phone',
        title: 'Built for mobile',
        text: 'The full journey lives in the app: book, track, and review on the go.',
    },
];
</script>

<template>
    <Head :title="`${appName} — Book your photoshoot`">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Syne:wght@500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="lp">
        <div class="lp__noise" aria-hidden="true" />
        <div class="lp__glow lp__glow--a" aria-hidden="true" />
        <div class="lp__glow lp__glow--b" aria-hidden="true" />

        <header class="lp__nav">
            <div class="lp__nav-inner">
                <a class="lp__brand" href="/" aria-label="Home">
                    <span class="lp__brand-icon"><i class="bi bi-aperture" aria-hidden="true" /></span>
                    <span class="lp__brand-text">
                        <span class="lp__brand-name">{{ appName }}</span>
                        <span class="lp__brand-tag">Photoshoots, simplified</span>
                    </span>
                </a>
                <nav class="lp__nav-actions" aria-label="Primary">
                    <a class="lp__link-ghost" href="/admin/login">Staff sign in</a>
                    <a class="lp__btn-outline" href="/admin/login">
                        <i class="bi bi-grid-1x2-fill" aria-hidden="true" />
                        Admin console
                    </a>
                </nav>
            </div>
        </header>

        <main>
            <section class="lp__hero">
                <div class="lp__hero-copy">
                    <p class="lp__eyebrow">For customers</p>
                    <h1 class="lp__title">
                        Light, lens, and a little less chaos.
                    </h1>
                    <p class="lp__lead">
                        Book a shoot that fits your day—choose a plan, lock a time, and pay securely while
                        verified partners in your city handle the rest.
                    </p>
                    <div class="lp__hero-badges">
                        <span class="lp__pill"><i class="bi bi-geo-alt" /> City-based partners</span>
                        <span class="lp__pill"><i class="bi bi-lock-fill" /> Staged payments</span>
                        <span class="lp__pill"><i class="bi bi-stars" /> One app end-to-end</span>
                    </div>
                </div>

                <div class="lp__hero-visual" aria-hidden="true">
                    <div class="lp__frame">
                        <div class="lp__frame-inner">
                            <div class="lp__frame-stripe" />
                            <div class="lp__frame-grid" />
                        </div>
                        <div class="lp__frame-corner lp__frame-corner--tl" />
                        <div class="lp__frame-corner lp__frame-corner--tr" />
                        <div class="lp__frame-corner lp__frame-corner--bl" />
                        <div class="lp__frame-corner lp__frame-corner--br" />
                    </div>
                    <p class="lp__frame-caption">Your story · framed</p>
                </div>
            </section>

            <section class="lp__band" aria-label="Highlights">
                <div class="lp__band-inner">
                    <span>Verified partners</span>
                    <span class="lp__band-dot" aria-hidden="true" />
                    <span>Clear pricing</span>
                    <span class="lp__band-dot" aria-hidden="true" />
                    <span>Mobile-first booking</span>
                    <span class="lp__band-dot" aria-hidden="true" />
                    <span>Results in one place</span>
                </div>
            </section>

            <section class="lp__section lp__section--steps">
                <h2 class="lp__section-title">How it works</h2>
                <p class="lp__section-sub">Three beats from idea to album.</p>
                <ol class="lp__steps">
                    <li v-for="s in steps" :key="s.n" class="lp__step">
                        <span class="lp__step-num">{{ s.n }}</span>
                        <h3 class="lp__step-title">{{ s.title }}</h3>
                        <p class="lp__step-text">{{ s.text }}</p>
                    </li>
                </ol>
            </section>

            <section class="lp__section">
                <h2 class="lp__section-title">Why people stay</h2>
                <p class="lp__section-sub">Trust and clarity—not buzzwords.</p>
                <div class="lp__grid">
                    <article v-for="p in pillars" :key="p.title" class="lp__card">
                        <div class="lp__card-icon"><i :class="['bi', p.icon]" aria-hidden="true" /></div>
                        <h3 class="lp__card-title">{{ p.title }}</h3>
                        <p class="lp__card-text">{{ p.text }}</p>
                    </article>
                </div>
            </section>

            <section class="lp__cta">
                <div class="lp__cta-inner">
                    <div>
                        <h2 class="lp__cta-title">Running operations or shoots?</h2>
                        <p class="lp__cta-text">
                            Cities, plans, bookings, partners, and payouts live behind a secure admin login—built
                            for your team, not the public gallery.
                        </p>
                    </div>
                    <a class="lp__btn-solid" href="/admin/login">
                        Open admin
                        <i class="bi bi-arrow-right-short" aria-hidden="true" />
                    </a>
                </div>
            </section>
        </main>

        <footer class="lp__footer">
            <p class="lp__footer-copy">
                © {{ new Date().getFullYear() }} {{ appName }}. Customer bookings happen in the mobile app.
            </p>
            <a class="lp__footer-link" href="/admin/login">Staff access</a>
        </footer>
    </div>
</template>

<style scoped>
.lp {
    --lp-bg: #07070c;
    --lp-surface: rgba(18, 18, 26, 0.72);
    --lp-border: rgba(255, 255, 255, 0.08);
    --lp-text: #f4f4f8;
    --lp-muted: #9ca3b0;
    --lp-accent: #e8b86d;
    --lp-accent-2: #f472b6;
    --lp-font: 'DM Sans', system-ui, sans-serif;
    --lp-display: 'Syne', system-ui, sans-serif;

    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
    background: var(--lp-bg);
    color: var(--lp-text);
    font-family: var(--lp-font);
    font-size: 1rem;
    line-height: 1.55;
}

.lp__noise {
    pointer-events: none;
    position: fixed;
    inset: 0;
    opacity: 0.04;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    z-index: 0;
}

.lp__glow {
    pointer-events: none;
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.35;
    z-index: 0;
}

.lp__glow--a {
    width: min(60vw, 520px);
    height: min(60vw, 520px);
    top: -12%;
    right: -8%;
    background: radial-gradient(circle, rgba(232, 184, 109, 0.55) 0%, transparent 70%);
}

.lp__glow--b {
    width: min(50vw, 420px);
    height: min(50vw, 420px);
    bottom: 5%;
    left: -10%;
    background: radial-gradient(circle, rgba(244, 114, 182, 0.28) 0%, transparent 65%);
}

.lp__nav {
    position: sticky;
    top: 0;
    z-index: 20;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--lp-border);
    background: rgba(7, 7, 12, 0.65);
    backdrop-filter: blur(16px);
}

.lp__nav-inner {
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.lp__brand {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    text-decoration: none;
    color: inherit;
    min-width: 0;
}

.lp__brand-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(232, 184, 109, 0.95), rgba(244, 114, 182, 0.75));
    color: #0a0a0f;
    font-size: 1.35rem;
    flex-shrink: 0;
    box-shadow: 0 12px 40px rgba(232, 184, 109, 0.25);
}

.lp__brand-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.lp__brand-name {
    font-family: var(--lp-display);
    font-weight: 700;
    font-size: 1.15rem;
    letter-spacing: -0.03em;
}

.lp__brand-tag {
    font-size: 0.78rem;
    color: var(--lp-muted);
}

.lp__nav-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-shrink: 0;
}

.lp__link-ghost {
    color: var(--lp-muted);
    text-decoration: none;
    font-size: 0.92rem;
    font-weight: 500;
    padding: 0.4rem 0.6rem;
    border-radius: 0.5rem;
    transition: color 0.15s ease, background 0.15s ease;
}

.lp__link-ghost:hover {
    color: var(--lp-text);
    background: rgba(255, 255, 255, 0.05);
}

.lp__btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1rem;
    border-radius: 999px;
    border: 1px solid var(--lp-border);
    color: var(--lp-text);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.03);
    transition: border-color 0.15s ease, background 0.15s ease;
}

.lp__btn-outline:hover {
    border-color: rgba(232, 184, 109, 0.45);
    background: rgba(232, 184, 109, 0.08);
}

.lp__hero {
    position: relative;
    z-index: 1;
    max-width: 1120px;
    margin: 0 auto;
    padding: clamp(2.5rem, 6vw, 4.5rem) 1.25rem 2rem;
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(260px, 0.85fr);
    gap: clamp(2rem, 5vw, 3.5rem);
    align-items: center;
}

.lp__eyebrow {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--lp-accent);
    margin: 0 0 1rem;
}

.lp__title {
    font-family: var(--lp-display);
    font-weight: 800;
    font-size: clamp(2.35rem, 5.5vw, 3.75rem);
    line-height: 1.02;
    letter-spacing: -0.045em;
    margin: 0 0 1.25rem;
    max-width: 14ch;
}

.lp__lead {
    margin: 0 0 1.75rem;
    color: var(--lp-muted);
    font-size: 1.08rem;
    max-width: 38ch;
}

.lp__hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.lp__pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 999px;
    font-size: 0.82rem;
    color: var(--lp-muted);
    border: 1px solid var(--lp-border);
    background: rgba(255, 255, 255, 0.02);
}

.lp__pill i {
    color: var(--lp-accent);
    font-size: 0.9rem;
}

.lp__hero-visual {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.lp__frame {
    position: relative;
    width: min(100%, 320px);
    aspect-ratio: 3 / 4;
    border-radius: 1.5rem;
    border: 1px solid var(--lp-border);
    background: var(--lp-surface);
    overflow: hidden;
    box-shadow:
        0 40px 80px rgba(0, 0, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.lp__frame-inner {
    position: absolute;
    inset: 12%;
    border-radius: 0.75rem;
    overflow: hidden;
    background: linear-gradient(165deg, #1a1520 0%, #0d0d12 50%, #12101a 100%);
}

.lp__frame-stripe {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        115deg,
        transparent 40%,
        rgba(232, 184, 109, 0.12) 48%,
        rgba(244, 114, 182, 0.1) 52%,
        transparent 60%
    );
    animation: lp-shimmer 8s ease-in-out infinite;
}

.lp__frame-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
    background-size: 24px 24px;
    opacity: 0.5;
}

@keyframes lp-shimmer {
    0%,
    100% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
}

.lp__frame-corner {
    position: absolute;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(232, 184, 109, 0.55);
}

.lp__frame-corner--tl {
    top: 10px;
    left: 10px;
    border-right: none;
    border-bottom: none;
}

.lp__frame-corner--tr {
    top: 10px;
    right: 10px;
    border-left: none;
    border-bottom: none;
}

.lp__frame-corner--bl {
    bottom: 10px;
    left: 10px;
    border-right: none;
    border-top: none;
}

.lp__frame-corner--br {
    bottom: 10px;
    right: 10px;
    border-left: none;
    border-top: none;
}

.lp__frame-caption {
    margin: 0;
    font-size: 0.78rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--lp-muted);
}

.lp__band {
    position: relative;
    z-index: 1;
    border-block: 1px solid var(--lp-border);
    background: rgba(255, 255, 255, 0.02);
    padding: 0.95rem 1.25rem;
}

.lp__band-inner {
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.75rem 1.25rem;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--lp-muted);
}

.lp__band-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--lp-accent);
    opacity: 0.7;
    flex-shrink: 0;
}

.lp__section {
    position: relative;
    z-index: 1;
    max-width: 1120px;
    margin: 0 auto;
    padding: clamp(2.5rem, 5vw, 4rem) 1.25rem;
}

.lp__section--steps {
    padding-bottom: 2rem;
}

.lp__section-title {
    font-family: var(--lp-display);
    font-size: clamp(1.65rem, 3vw, 2.25rem);
    font-weight: 700;
    letter-spacing: -0.03em;
    margin: 0 0 0.5rem;
}

.lp__section-sub {
    margin: 0 0 2rem;
    color: var(--lp-muted);
    font-size: 1.02rem;
    max-width: 40ch;
}

.lp__steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.lp__step {
    padding: 1.5rem;
    border-radius: 1.25rem;
    border: 1px solid var(--lp-border);
    background: var(--lp-surface);
    backdrop-filter: blur(12px);
}

.lp__step-num {
    display: block;
    font-family: var(--lp-display);
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--lp-accent);
    margin-bottom: 0.75rem;
    letter-spacing: 0.08em;
}

.lp__step-title {
    font-family: var(--lp-display);
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
}

.lp__step-text {
    margin: 0;
    color: var(--lp-muted);
    font-size: 0.95rem;
}

.lp__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.lp__card {
    padding: 1.5rem;
    border-radius: 1.25rem;
    border: 1px solid var(--lp-border);
    background: linear-gradient(160deg, rgba(24, 24, 34, 0.9), rgba(12, 12, 18, 0.95));
}

.lp__card-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    color: #0a0a0f;
    background: linear-gradient(135deg, var(--lp-accent), var(--lp-accent-2));
}

.lp__card-title {
    font-family: var(--lp-display);
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 0.45rem;
}

.lp__card-text {
    margin: 0;
    color: var(--lp-muted);
    font-size: 0.94rem;
}

.lp__cta {
    position: relative;
    z-index: 1;
    max-width: 1120px;
    margin: 0 auto 2rem;
    padding: 0 1.25rem;
}

.lp__cta-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: clamp(1.75rem, 4vw, 2.25rem);
    border-radius: 1.5rem;
    border: 1px solid var(--lp-border);
    background: linear-gradient(135deg, rgba(232, 184, 109, 0.12), rgba(244, 114, 182, 0.08));
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
}

.lp__cta-title {
    font-family: var(--lp-display);
    font-size: clamp(1.25rem, 2.5vw, 1.6rem);
    font-weight: 700;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
}

.lp__cta-text {
    margin: 0;
    color: var(--lp-muted);
    max-width: 46ch;
    font-size: 0.98rem;
}

.lp__btn-solid {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.75rem 1.35rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    color: #0a0a0f;
    background: linear-gradient(135deg, #f0d399, #e8b86d);
    border: none;
    box-shadow: 0 14px 36px rgba(232, 184, 109, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.lp__btn-solid:hover {
    transform: translateY(-1px);
    box-shadow: 0 18px 44px rgba(232, 184, 109, 0.45);
    color: #0a0a0f;
}

.lp__btn-solid i {
    font-size: 1.35rem;
    margin-left: 0.15rem;
}

.lp__footer {
    position: relative;
    z-index: 1;
    max-width: 1120px;
    margin: 0 auto;
    padding: 2rem 1.25rem 2.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-top: 1px solid var(--lp-border);
    font-size: 0.88rem;
    color: var(--lp-muted);
}

.lp__footer-copy {
    margin: 0;
    max-width: 46ch;
}

.lp__footer-link {
    color: var(--lp-accent);
    text-decoration: none;
    font-weight: 600;
}

.lp__footer-link:hover {
    text-decoration: underline;
}

@media (prefers-reduced-motion: reduce) {
    .lp__frame-stripe {
        animation: none;
    }
}

@media (max-width: 991px) {
    .lp__hero {
        grid-template-columns: 1fr;
    }

    .lp__hero-visual {
        order: -1;
    }

    .lp__title {
        max-width: none;
    }

    .lp__steps,
    .lp__grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575px) {
    .lp__nav-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .lp__link-ghost {
        text-align: center;
    }

    .lp__btn-outline {
        justify-content: center;
    }

    .lp__cta-inner {
        flex-direction: column;
        align-items: stretch;
    }

    .lp__btn-solid {
        justify-content: center;
    }
}
</style>
