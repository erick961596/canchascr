<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'SuperCancha Owner')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; }
        :root {
            --sidebar-w: 260px;
            --header-h: 64px;
            --primary: #000;
            --accent: #6C63FF;
        }
        body { background: #F4F6FA; }

        /* ---- SIDEBAR ---- */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: #fff;
            border-right: 1px solid #f0f0f0;
            display: flex; flex-direction: column;
            z-index: 1040;
            transition: transform .3s ease;
        }
        .sidebar-brand {
            height: var(--header-h);
            display: flex; align-items: center; padding: 0 20px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 800; font-size: 20px; letter-spacing: -0.5px;
        }
        .sidebar-brand span { color: var(--accent); }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .sidebar-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1px;
            color: #aaa; padding: 16px 20px 4px; text-transform: uppercase;
        }
        .nav-link-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 20px; color: #555; text-decoration: none;
            font-size: 14px; font-weight: 500; border-radius: 0;
            transition: all .2s; margin: 1px 8px; border-radius: 10px;
        }
        .nav-link-item i { width: 20px; text-align: center; font-size: 16px; }
        .nav-link-item:hover { background: #f5f5f5; color: #000; }
        .nav-link-item.active { background: #000; color: #fff; }
        .sidebar-footer { padding: 16px; border-top: 1px solid #f0f0f0; }

        /* ---- MAIN ---- */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            height: var(--header-h); background: #fff;
            border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; padding: 0 24px;
            gap: 16px; position: sticky; top: 0; z-index: 1030;
        }
        .page-content { padding: 28px 24px; flex: 1; }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 20px;
            border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .table-modern { border-collapse: separate; border-spacing: 0 6px; }
        .table-modern thead th {
            font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: #888; border: none; padding: 8px 12px;
        }
        .table-modern tbody tr {
            background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .table-modern tbody tr td {
            padding: 12px; vertical-align: middle; border: none;
        }
        .table-modern tbody tr td:first-child { border-radius: 10px 0 0 10px; }
        .table-modern tbody tr td:last-child  { border-radius: 0 10px 10px 0; }
        .badge-status { border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 600; }

        /* mobile sidebar toggle */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 1039;
        }
        .sidebar-overlay.open { display: block; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        Super<span>Cancha</span>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-label">Principal</div>
        <a href="{{ route('owner.dashboard') }}" class="nav-link-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>

        <div class="sidebar-label">Gestión</div>
        <a href="{{ route('owner.venues.index') }}" class="nav-link-item {{ request()->routeIs('owner.venues.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building"></i> Mis Sedes
        </a>
        <a href="{{ route('owner.courts.index') }}" class="nav-link-item {{ request()->routeIs('owner.courts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-futbol"></i> Canchas
        </a>
        <a href="{{ route('owner.reservations.index') }}" class="nav-link-item {{ request()->routeIs('owner.reservations.*') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i> Reservas
        </a>
        <a href="{{ route('owner.services.index') }}" class="nav-link-item {{ request()->routeIs('owner.services.*') ? 'active' : '' }}">
            <i class="fa-solid fa-concierge-bell"></i> Servicios
        </a>
        <a href="{{ route('owner.promotions.index') }}" class="nav-link-item {{ request()->routeIs('owner.promotions.*') ? 'active' : '' }}">
            <i class="fa-solid fa-tag"></i> Promociones
        </a>

        <div class="sidebar-label">Cuenta</div>
        <a href="{{ route('owner.subscription.index') }}" class="nav-link-item {{ request()->routeIs('owner.subscription.*') ? 'active' : '' }}">
            <i class="fa-solid fa-crown"></i> Suscripción
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-3">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar }}" class="rounded-circle" width="36" height="36">
            @else
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-weight:700">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="font-size:13px;font-weight:600">{{ auth()->user()->name }}</div>
                <div style="font-size:11px;color:#888">Owner</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="mt-3">
            @csrf
            <button class="btn btn-sm w-100" style="border:1px solid #e0e0e0;border-radius:10px;font-size:12px;">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>

{{-- MAIN --}}
<div class="main-wrapper">
    <header class="topbar">
        <button class="btn btn-sm d-lg-none" onclick="openSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="ms-auto d-flex align-items-center gap-3">
            @php $sub = auth()->user()->subscription; @endphp
            @if($sub?->isActive())
                <span class="badge" style="background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:6px 12px;font-size:12px;">
                    <i class="fa-solid fa-circle-check me-1"></i>
                    Plan {{ $sub->plan?->name }} · vence {{ $sub->ends_at?->format('d/m/Y') }}
                </span>
            @else
                <a href="{{ route('owner.subscription.index') }}" class="badge text-decoration-none" style="background:#fff3e0;color:#e65100;border-radius:20px;padding:6px 12px;font-size:12px;">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin suscripción activa
                </a>
            @endif
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert border-0 mb-4" style="background:#e8f5e9;color:#2e7d32;border-radius:14px;">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert border-0 mb-4" style="background:#fff3e0;color:#e65100;border-radius:14px;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRF;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    const Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3500,
        timerProgressBar: true,
    });

    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }
</script>

@stack('scripts')
</body>
</html>
