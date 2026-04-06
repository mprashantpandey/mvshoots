@php
    $links = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid'],
        ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => 'bi-collection'],
        ['route' => 'admin.plans.index', 'label' => 'Plans', 'icon' => 'bi-card-checklist'],
        ['route' => 'admin.reels.index', 'label' => 'Reels', 'icon' => 'bi-play-btn'],
        ['route' => 'admin.bookings.index', 'label' => 'Bookings', 'icon' => 'bi-calendar2-check'],
        ['route' => 'admin.booking-results.index', 'label' => 'Results', 'icon' => 'bi-images'],
        ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => 'bi-credit-card-2-front'],
        ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'bi-people'],
        ['route' => 'admin.partners.index', 'label' => 'Partners', 'icon' => 'bi-camera-reels'],
        ['route' => 'admin.owners.index', 'label' => 'Owners', 'icon' => 'bi-person-badge'],
        ['route' => 'admin.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
        ['route' => 'admin.reports.index', 'label' => 'Reports', 'icon' => 'bi-bar-chart'],
        ['route' => 'admin.settings.edit', 'label' => 'Settings', 'icon' => 'bi-gear'],
    ];
@endphp

<div class="d-flex align-items-center gap-3 mb-4">
    <div class="brand-mark"><i class="bi bi-camera-fill"></i></div>
    <div>
        <div class="fw-bold">{{ \App\Models\Setting::value('app_name', 'VM Shoot') }}</div>
        <div class="small text-white-50">Admin Console</div>
    </div>
</div>

<div class="soft-section-title">Main Menu</div>
<nav class="nav flex-column">
    @foreach($links as $link)
        <a
            href="{{ route($link['route']) }}"
            class="nav-link {{ request()->routeIs(str_replace('index', '*', $link['route'])) || request()->routeIs($link['route']) ? 'active' : '' }}"
        >
            <i class="bi {{ $link['icon'] }}"></i>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>
