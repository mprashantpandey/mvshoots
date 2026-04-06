<div class="topbar d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <div class="text-secondary small">Premium responsive dashboard</div>
            <h1 class="h4 mb-0">@yield('page-title', 'Admin Panel')</h1>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a class="btn btn-outline-secondary" href="{{ route('admin.notifications.index') }}">
            <i class="bi bi-bell me-2"></i>Notifications
        </a>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-circle">{{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="fw-semibold">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
                <div class="small text-secondary">{{ auth('admin')->user()->email ?? '' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn btn-dark" type="submit">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </form>
    </div>
</div>
