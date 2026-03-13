<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><title>Registro - SuperCancha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family:'Inter',sans-serif; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: #fff; border-radius: 28px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); }
        .brand { font-size: 28px; font-weight: 800; letter-spacing: -1px; }
        .brand span { color: #6C63FF; }
        .form-control { border-radius: 12px; border: 1.5px solid #e8e8e8; padding: 12px 16px; font-size: 14px; }
        .form-control:focus { border-color: #000; box-shadow: none; }
        .btn-primary-custom { background: #000; color: #fff; border: none; border-radius: 14px; padding: 13px; font-weight: 700; font-size: 15px; width: 100%; }
        .btn-google { background: #fff; border: 1.5px solid #e8e8e8; border-radius: 14px; padding: 12px; font-weight: 600; font-size: 14px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; color: #333; text-decoration: none; }
        .btn-google:hover { border-color: #aaa; color: #333; }
        .divider { display: flex; align-items: center; gap: 12px; color: #bbb; font-size: 13px; margin: 20px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand mb-2">Super<span>Cancha</span></div>
        <p class="text-muted mb-4" style="font-size:14px">Creá tu cuenta para reservar canchas.</p>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-3" style="font-size:13px">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
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
                <label class="form-label fw-600" style="font-size:13px">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-600" style="font-size:13px">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary-custom mb-3">Crear cuenta</button>
        </form>

        <div class="divider">o</div>
        <a href="{{ route('auth.google') }}" class="btn-google mb-4">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="20" alt="Google">
            Continuar con Google
        </a>

        <p class="text-center text-muted mb-0" style="font-size:13px">
            ¿Ya tenés cuenta? <a href="{{ route('login') }}" style="color:#000;font-weight:700;text-decoration:none">Iniciá sesión</a>
        </p>
    </div>
</body>
</html>
