<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin - SuperCancha')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        :root { --sidebar-w: 260px; --header-h: 64px; }
        body { background: #F4F6FA; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-w); background: #0f0f0f; display: flex; flex-direction: column; z-index: 1040; overflow-y: auto; }
        .sidebar-brand { height: var(--header-h); display: flex; align-items: center; padding: 0 20px; border-bottom: 1px solid #222; font-weight: 800; font-size: 20px; color: #fff; flex-shrink: 0; }
        .sidebar-brand span { color: #6C63FF; }
        .sidebar-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; color: #555; padding: 16px 20px 4px; text-transform: uppercase; }
        .nav-link-item { display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: #888; text-decoration: none; font-size: 14px; font-weight: 500; margin: 1px 8px; border-radius: 10px; transition: all .2s; }
        .nav-link-item i { width: 20px; text-align: center; font-size: 16px; }
        .nav-link-item:hover { background: #1a1a1a; color: #fff; }
        .nav-link-item.active { background: #6C63FF; color: #fff; }
        .sidebar-footer { padding: 16px; border-top: 1px solid #222; flex-shrink: 0; }
        .main-wrapper { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar { height: var(--header-h); background: #fff; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; padding: 0 24px; position: sticky; top: 0; z-index: 1030; }
        .page-content { padding: 28px 24px; }
        .stat-card { background: #fff; border-radius: 16px; padding: 20px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
        .table-modern { border-collapse: separate; border-spacing: 0 6px; }
        .table-modern thead th { font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #888; border: none; padding: 8px 12px; }
        .table-modern tbody tr { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .table-modern tbody tr td { padding: 12px; vertical-align: middle; border: none; }
        .table-modern tbody tr td:first-child { border-radius: 10px 0 0 10px; }
        .table-modern tbody tr td:last-child { border-radius: 0 10px 10px 0; }
        .badge-active   { background:#e8f5e9;color:#2e7d32;border-radius:20px;font-size:11px;padding:4px 10px }
        .badge-pending  { background:#fff3e0;color:#e65100;border-radius:20px;font-size:11px;padding:4px 10px }
        .badge-inactive { background:#f5f5f5;color:#888;border-radius:20px;font-size:11px;padding:4px 10px }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">Super<span>Cancha</span> <small style="font-size:11px;color:#555;margin-left:6px">Admin</small></div>
    <nav style="flex:1;padding:12px 0">
        <div class="sidebar-label">Principal</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-line"></i> Dashboard
        </a>

        <div class="sidebar-label">Gestión</div>
        <a href="{{ route('admin.users.index') }}" class="nav-link-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i> Usuarios
        </a>
        <a href="{{ route('admin.venues.index') }}" class="nav-link-item {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building"></i> Sedes
        </a>
        <a href="{{ route('admin.plans.index') }}" class="nav-link-item {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i> Planes
        </a>
        <a href="{{ route('admin.subscriptions.index') }}" class="nav-link-item {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
            <i class="fa-solid fa-crown"></i> Suscripciones
        </a>
        <a href="{{ route('admin.logs.index') }}" class="nav-link-item {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
            <i class="fa-solid fa-terminal"></i> Logs del sistema
        </a>
        <a href="{{ route('admin.subscriptions.pending') }}" class="nav-link-item {{ request()->routeIs('admin.subscriptions.pending') ? 'active' : '' }}">
            <i class="fa-solid fa-clock"></i> Pagos pendientes
            @php $pendingCount = \App\Models\SubscriptionPayment::where('status','pending')->where('method','manual')->count(); @endphp
            @if($pendingCount > 0)
            <span class="ms-auto badge" style="background:#ff5252;color:#fff;border-radius:20px;font-size:10px;padding:2px 7px">{{ $pendingCount }}</span>
            @endif
        </a>
    </nav>
    <div class="sidebar-footer">
        <div style="color:#888;font-size:12px;margin-bottom:8px">
            <i class="fa-solid fa-circle-user me-1"></i>{{ auth()->user()->name }}
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-sm w-100" style="border:1px solid #333;color:#888;border-radius:10px;font-size:12px;">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <h6 class="mb-0 fw-700">@yield('page_title', 'Dashboard')</h6>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge rounded-pill" style="background:#f0f0f0;color:#555;font-size:12px">
                <i class="fa-solid fa-shield-halved me-1 text-danger"></i>Admin
            </span>
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert border-0 mb-4" style="background:#e8f5e9;color:#2e7d32;border-radius:14px;">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert border-0 mb-4" style="background:#ffebee;color:#c62828;border-radius:14px;">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRF;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, timerProgressBar:true });
</script>
@stack('scripts')
</body>
</html>
