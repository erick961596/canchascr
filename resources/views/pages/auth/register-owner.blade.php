<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><title>Registrá tu cancha - SuperCancha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family:'Inter',sans-serif; }
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .wrapper { display: grid; grid-template-columns: 1fr 1fr; max-width: 900px; width: 100%; border-radius: 28px; overflow: hidden; box-shadow: 0 40px 80px rgba(0,0,0,0.4); }
        .hero { background: linear-gradient(180deg, #6C63FF 0%, #4A42CC 100%); padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }
        .hero h2 { color: #fff; font-size: 28px; font-weight: 800; line-height: 1.3; }
        .hero p { color: rgba(255,255,255,0.75); font-size: 14px; margin-top: 12px; }
        .hero-feature { display: flex; gap: 12px; align-items: flex-start; margin-top: 20px; }
        .hero-feature .icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .hero-feature p { color: rgba(255,255,255,0.8); font-size: 13px; margin: 0; }
        .hero-feature strong { color: #fff; display: block; font-size: 14px; }
        .form-side { background: #fff; padding: 48px 40px; }
        .brand { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px; }
        .brand span { color: #6C63FF; }
        .form-control { border-radius: 12px; border: 1.5px solid #e8e8e8; padding: 12px 16px; font-size: 14px; }
        .form-control:focus { border-color: #6C63FF; box-shadow: none; }
        .btn-owner { background: #6C63FF; color: #fff; border: none; border-radius: 14px; padding: 13px; font-weight: 700; font-size: 15px; width: 100%; }
        .btn-owner:hover { background: #5a52dd; color: #fff; }
        @media (max-width: 767px) { .wrapper { grid-template-columns: 1fr; } .hero { display: none; } }
    </style>
</head>
<body>
    <div class="wrapper">
        {{-- Hero left side --}}
        <div class="hero">
            <div class="brand" style="color:#fff">Super<span style="color:rgba(255,255,255,0.7)">Cancha</span></div>
            <h2>Gestioná tu cancha desde un solo lugar</h2>
            <p>Publicá tus canchas, manejá horarios y recibí reservas. Todo digital.</p>

            <div class="hero-feature">
                <div class="icon"><i class="fa-solid fa-calendar-check" style="color:#fff;font-size:14px"></i></div>
                <div><strong>Reservas en tiempo real</strong><p>Tus clientes reservan directamente desde la app.</p></div>
            </div>
            <div class="hero-feature">
                <div class="icon"><i class="fa-solid fa-chart-bar" style="color:#fff;font-size:14px"></i></div>
                <div><strong>Dashboard con estadísticas</strong><p>Visualizá ocupación, ingresos y más.</p></div>
            </div>
            <div class="hero-feature">
                <div class="icon"><i class="fa-solid fa-bell" style="color:#fff;font-size:14px"></i></div>
                <div><strong>Notificaciones automáticas</strong><p>Email para vos y tus clientes en cada reserva.</p></div>
            </div>
        </div>

        {{-- Form right side --}}
        <div class="form-side">
            <div class="brand">Super<span>Cancha</span></div>
            <p class="text-muted mb-4" style="font-size:14px">Creá tu cuenta de dueño de cancha.</p>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-3" style="font-size:13px">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.owner') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:13px">Nombre completo</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:13px">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:13px">Teléfono (opcional)</label>
                    <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="8888-8888">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:13px">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-600" style="font-size:13px">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn-owner">Crear cuenta de dueño →</button>
            </form>

            <p class="text-center text-muted mt-4 mb-0" style="font-size:13px">
                ¿Ya tenés cuenta? <a href="{{ route('login') }}" style="color:#6C63FF;font-weight:700;text-decoration:none">Iniciá sesión</a>
            </p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>
