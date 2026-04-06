<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($title ?? 'Dashboard') . ' | ' . \App\Models\Setting::value('app_name', 'VM Shoot Admin') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --admin-bg: #f4f7fb;
            --admin-surface: rgba(255, 255, 255, 0.9);
            --admin-border: rgba(148, 163, 184, 0.18);
            --admin-text: #0f172a;
            --admin-muted: #667085;
            --admin-primary: #2563eb;
            --admin-secondary: #10b981;
            --admin-sidebar: #0f172a;
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.12), transparent 24%),
                var(--admin-bg);
            color: var(--admin-text);
        }
        body, .form-control, .form-select, .btn {
            font-family: "Inter", "Segoe UI", sans-serif;
        }
        .app-shell {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a, #111827 60%, #172033);
            color: #fff;
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 1.5rem 1.1rem;
            overflow-y: auto;
        }
        .sidebar .brand-mark {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #60a5fa, #34d399);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 18px 40px rgba(96, 165, 250, 0.22);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            border-radius: 16px;
            padding: 0.8rem 0.95rem;
            display: flex;
            gap: 0.85rem;
            align-items: center;
            margin-bottom: 0.35rem;
        }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }
        .content-area {
            flex: 1;
            min-width: 0;
            padding: 1.5rem;
        }
        .glass-card,
        .table-card {
            border: 1px solid var(--admin-border);
            border-radius: 28px;
            background: var(--admin-surface);
            backdrop-filter: blur(12px);
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
        }
        .table-card {
            overflow: hidden;
        }
        .stat-card {
            padding: 1.25rem;
            height: 100%;
        }
        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, 0.1);
            color: var(--admin-primary);
        }
        .topbar {
            border: 1px solid var(--admin-border);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.74);
            backdrop-filter: blur(18px);
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }
        .badge-soft {
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-weight: 600;
        }
        .table thead th {
            color: var(--admin-muted);
            font-weight: 600;
            background: rgba(248, 250, 252, 0.8);
            border-bottom-width: 1px;
        }
        .table td, .table th {
            vertical-align: middle;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .form-control,
        .form-select {
            border-radius: 16px;
            border-color: rgba(148, 163, 184, 0.3);
            min-height: 48px;
        }
        textarea.form-control {
            min-height: 130px;
        }
        .btn {
            border-radius: 14px;
            padding: 0.7rem 1rem;
            font-weight: 600;
        }
        .soft-section-title {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.48);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 1.4rem 0 0.75rem;
        }
        .mobile-sidebar {
            background: rgba(15, 23, 42, 0.98);
            color: #fff;
        }
        .avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #dcfce7);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #1d4ed8;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }
        @media (max-width: 991.98px) {
            .content-area {
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar d-none d-lg-block">
            @include('admin.partials.sidebar')
        </aside>

        <div class="content-area">
            @include('admin.partials.topbar')
            @include('admin.partials.alerts')
            @yield('content')
        </div>
    </div>

    <div class="offcanvas offcanvas-start mobile-sidebar d-lg-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header border-bottom border-secondary-subtle">
            <h5 class="offcanvas-title">VM Shoot Admin</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @include('admin.partials.sidebar')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
