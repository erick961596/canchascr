<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'SuperCancha')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #F4F6FA; padding-bottom: 100px; }
        .badge-sport { border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 600; }
        .card-venue { border-radius: 20px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.07); overflow: hidden; transition: transform .2s; }
        .card-venue:hover { transform: translateY(-4px); }
        .btn-black { background: #000; color: #fff; border-radius: 14px; font-weight: 600; }
        .btn-black:hover { background: #222; color: #fff; }
        .avatar-sm { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .slot-btn { border-radius: 10px; border: 2px solid #e0e0e0; background: #fff; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; }
        .slot-btn:hover { border-color: #000; }
        .slot-btn.selected { background: #000; color: #fff; border-color: #000; }
        .slot-btn.unavailable { opacity: .4; cursor: not-allowed; background: #f0f0f0; }
    </style>

    @stack('styles')
</head>
<body>

<div class="d-flex flex-column min-vh-100">
    @includeIf('pages.player.partials._header')

    <main class="flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success mx-3 mt-3 border-0 rounded-3">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning mx-3 mt-3 border-0 rounded-3">{{ session('warning') }}</div>
        @endif
        @yield('content')
    </main>

    @includeIf('pages.player.partials._bottom_nav')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
    if (window.axios) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRF;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }

    const Toast = Swal.mixin({
        toast: true, position: 'bottom-end',
        showConfirmButton: false, timer: 3500,
        timerProgressBar: true,
    });
</script>

@stack('scripts')
</body>
</html>
