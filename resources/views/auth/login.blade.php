<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - VACA SYS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 30%, #0891b2 70%, #0e7490 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '🥛';
            position: absolute;
            font-size: 400px;
            opacity: 0.05;
            top: -100px; right: -100px;
            transform: rotate(-15deg);
        }
        body::after {
            content: '🌾';
            position: absolute;
            font-size: 300px;
            opacity: 0.05;
            bottom: -80px; left: -80px;
            transform: rotate(10deg);
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
        .brand {
            text-align: center; margin-bottom: 32px;
        }
        .brand-logo {
            width: 76px; height: 76px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            font-size: 38px;
            margin: 0 auto 16px;
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4);
        }
        .brand-name {
            font-size: 30px; font-weight: 800; letter-spacing: -1px;
            background: linear-gradient(135deg, #065f46, #0891b2);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-name span { color: #f59e0b; -webkit-text-fill-color: #f59e0b; }
        .brand-sub {
            font-size: 14px; color: #64748b; margin-top: 4px; font-weight: 500;
        }
        .login-title {
            font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 6px;
        }
        .login-subtitle {
            font-size: 13.5px; color: #64748b; margin-bottom: 26px;
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
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
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
            font-size: 13px; font-weight: 600; color: #059669; text-decoration: none;
        }
        .forgot:hover { text-decoration: underline; }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(16, 185, 129, 0.45);
        }
        .btn-login:active { transform: translateY(0); }
        .divider {
            display: flex; align-items: center; gap: 12px; margin: 24px 0;
            color: #cbd5e1; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }
        .quick-access {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
        }
        .quick-btn {
            padding: 11px 10px;
            border-radius: 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 12px; font-weight: 600; color: #475569;
            cursor: pointer;
            text-decoration: none;
            display: flex; align-items: center; gap: 7px;
            transition: all 0.2s;
        }
        .quick-btn:hover { background: #fff; border-color: #10b981; color: #059669; transform: translateY(-1px); }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .home-link {
            text-align: center; margin-top: 24px;
        }
        .home-link a { color: #64748b; font-size: 13px; text-decoration: none; font-weight: 500; }
        .home-link a:hover { color: #059669; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <div class="brand-logo">🥛</div>
            <div class="brand-name">VACA<span>SYS</span></div>
            <div class="brand-sub">Sistema Integral de Gestión Láctea</div>
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
            <button type="button" class="quick-btn" onclick="quickFill('admin@vaca.pe','admin123')">👑 Administrador</button>
            <button type="button" class="quick-btn" onclick="quickFill('gerente@vaca.pe','gerente123')">📊 Gerente</button>
            <button type="button" class="quick-btn" onclick="quickFill('acopiador@vaca.pe','acopiador123')">🚛 Acopiador</button>
            <button type="button" class="quick-btn" onclick="quickFill('calidad@vaca.pe','calidad123')">🧪 Calidad</button>
            <button type="button" class="quick-btn" onclick="quickFill('planta@vaca.pe','planta123')">🏭 Planta</button>
            <button type="button" class="quick-btn" onclick="quickFill('productor@vaca.pe','productor123')">👨‍🌾 Productor</button>
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
