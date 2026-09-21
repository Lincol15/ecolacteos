<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Ecolácteos Huata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-ecolacteos.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at 15% 15%, #0BAA72 0%, #075B3A 45%, #054529 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute; top: -140px; right: -140px;
            width: 460px; height: 460px; border-radius: 50%;
            background: radial-gradient(circle, rgba(242,201,76,0.18), transparent 70%);
        }
        body::after {
            content: '';
            position: absolute; bottom: -160px; left: -120px;
            width: 420px; height: 420px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 70%);
        }
        .login-card {
            width: 100%; max-width: 440px;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            padding: 42px 38px;
            box-shadow: 0 25px 60px -12px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.2);
            position: relative;
            z-index: 10;
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        h1, h2, h3, .brand-name { font-family: 'Poppins', 'Inter', sans-serif; }
        .brand {
            text-align: center; margin-bottom: 32px;
        }
        .brand-logo {
            width: 84px; height: 84px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 16px;
            box-shadow: 0 12px 30px rgba(7, 91, 58, 0.35);
            border: 3px solid #fff;
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .brand-name {
            font-size: 28px; font-weight: 800; letter-spacing: -0.8px;
            color: #075B3A;
        }
        .brand-name span { color: #D6A927; }
        .brand-sub {
            font-size: 14px; color: #5C6E64; margin-top: 4px; font-weight: 500;
        }
        .brand-byline {
            font-size: 11px; color: #94a3b8; margin-top: 2px; font-weight: 600; letter-spacing: 0.5px;
        }
        .login-title {
            font-size: 22px; font-weight: 700; color: #24332D; margin-bottom: 6px;
        }
        .login-subtitle {
            font-size: 13.5px; color: #5C6E64; margin-bottom: 26px;
        }
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 600; color: #334155;
            margin-bottom: 7px;
        }
        .form-input {
            width: 100%;
            padding: 13px 15px 13px 44px;
            border-radius: 12px;
            border: 1.5px solid #E3EEE8;
            font-size: 14px;
            background: #F6FBF8;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #0BAA72;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(11, 170, 114, 0.14);
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 15px; top: 50%; transform: translateY(-50%);
            font-size: 17px; opacity: 0.55;
        }
        .row-between {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 22px;
        }
        .remember {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #475569; font-weight: 500;
            cursor: pointer;
        }
        .forgot {
            font-size: 13px; font-weight: 600; color: #075B3A; text-decoration: none;
        }
        .forgot:hover { text-decoration: underline; }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #075B3A;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 8px 20px rgba(7, 91, 58, 0.35);
        }
        .btn-login:hover {
            background: #054529;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(7, 91, 58, 0.45);
        }
        .btn-login:active { transform: translateY(0); }
        .divider {
            display: flex; align-items: center; gap: 12px; margin: 24px 0;
            color: #cbd5e1; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #E3EEE8;
        }
        .quick-access {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
        }
        .quick-btn {
            padding: 11px 10px;
            border-radius: 10px;
            background: #F6FBF8;
            border: 1px solid #E3EEE8;
            font-size: 12px; font-weight: 600; color: #475569;
            cursor: pointer;
            text-decoration: none;
            display: flex; align-items: center; gap: 7px;
            transition: all 0.2s;
        }
        .quick-btn:hover { background: #fff; border-color: #0BAA72; color: #075B3A; transform: translateY(-1px); }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #DDF5E9; color: #075B3A; border: 1px solid #0BAA72; }
        .home-link {
            text-align: center; margin-top: 24px;
        }
        .home-link a { color: #5C6E64; font-size: 13px; text-decoration: none; font-weight: 500; }
        .home-link a:hover { color: #075B3A; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <div class="brand-logo">
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
            </div>
            <div class="brand-name">Ecolácteos<span> Huata</span></div>
            <div class="brand-sub">Sistema de Gestión y Acopio de Productos Lácteos</div>
            <div class="brand-byline">by 4bytes</div>
        </div>

        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                ❌
                <div>
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            </div>
        @endif

        <h2 class="login-title">¡Bienvenido de nuevo! 👋</h2>
        <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

        <form method="POST" action="{{ route('authenticate') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email / DNI</label>
                <div class="input-wrap">
                    <span class="input-icon">📧</span>
                    <input type="text" name="email" class="form-input" placeholder="correo@ejemplo.com o DNI" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>
            <div class="row-between">
                <label class="remember">
                    <input type="checkbox" name="remember"> Recordarme
                </label>
                <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="btn-login">Ingresar al Sistema →</button>
        </form>

        <div class="divider">Accesos Demo</div>
        <div class="quick-access">
            <button type="button" class="quick-btn" onclick="quickFill('admin@ecolacteos.com','admin123')">👑 Administrador</button>
            <button type="button" class="quick-btn" onclick="quickFill('gerente@ecolacteos.com','gerente123')">📊 Gerente</button>
            <button type="button" class="quick-btn" onclick="quickFill('acopiador@ecolacteos.com','acopiador123')">🚛 Acopiador</button>
            <button type="button" class="quick-btn" onclick="quickFill('calidad@ecolacteos.com','calidad123')">🧪 Calidad</button>
            <button type="button" class="quick-btn" onclick="quickFill('planta@ecolacteos.com','planta123')">🏭 Planta</button>
            <button type="button" class="quick-btn" onclick="quickFill('productor1@ecolacteos.com','productor123')">👨‍🌾 Productor</button>
        </div>

        <div class="home-link">
            <a href="{{ route('home') }}">← Volver al inicio público</a>
        </div>
    </div>

    <script>
        function quickFill(email, pass) {
            document.querySelector('input[name=email]').value = email;
            document.querySelector('input[name=password]').value = pass;
        }
    </script>
</body>
</html>
