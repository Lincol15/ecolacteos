<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Ecolácteos Huata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-ecolacteos.png') }}">
    <style>
        :root {
            --green-900: #043521;
            --green-800: #054529;
            --green-700: #075B3A;
            --green-500: #0BAA72;
            --gold: #F2C94C;
            --gold-dark: #D6A927;
            --text: #1B2A23;
            --muted: #62736A;
            --line: #DCE7E1;
            --field: #F7FAF8;
            --danger: #B42318;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text);
            background: #fff;
            -webkit-font-smoothing: antialiased;
        }
        .auth {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            min-height: 100vh;
        }

        /* Panel de marca */
        .brand-panel {
            position: relative;
            overflow: hidden;
            color: #fff;
            padding: 48px 56px;
            display: flex; flex-direction: column; justify-content: space-between;
            background:
                linear-gradient(160deg, rgba(4, 53, 33, 0.92) 0%, rgba(7, 91, 58, 0.82) 55%, rgba(11, 170, 114, 0.65) 100%),
                url('{{ asset('images/planta-produccion.jpg') }}') center / cover no-repeat;
        }
        .brand-panel::after {
            content: '';
            position: absolute; right: -180px; bottom: -180px;
            width: 460px; height: 460px; border-radius: 50%;
            background: radial-gradient(circle, rgba(242, 201, 76, 0.22), transparent 70%);
            pointer-events: none;
        }
        .brand-top { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .brand-top img {
            width: 52px; height: 52px; border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.85);
            object-fit: cover;
        }
        .brand-name { font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; line-height: 1.1; }
        .brand-name span { color: var(--gold); }
        .brand-tag { font-size: 12px; opacity: 0.75; font-weight: 500; }

        .brand-copy { position: relative; z-index: 1; max-width: 460px; }
        .brand-copy h1 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(30px, 3.2vw, 42px);
            font-weight: 800; line-height: 1.12; letter-spacing: -1px;
            margin-bottom: 16px;
        }
        .brand-copy h1 span { color: var(--gold); }
        .brand-copy p { font-size: 15.5px; line-height: 1.65; opacity: 0.85; margin-bottom: 30px; }
        .features { list-style: none; display: grid; gap: 14px; }
        .features li { display: flex; align-items: center; gap: 12px; font-size: 14.5px; font-weight: 500; }
        .features .tick {
            flex: none; width: 30px; height: 30px; border-radius: 9px;
            display: grid; place-items: center;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .features svg { width: 16px; height: 16px; color: var(--gold); }
        .brand-foot { position: relative; z-index: 1; font-size: 12.5px; opacity: 0.65; }

        /* Panel del formulario */
        .form-panel {
            display: flex; flex-direction: column;
            padding: 32px 48px;
            background:
                radial-gradient(circle at 100% 0%, rgba(11, 170, 114, 0.06), transparent 40%),
                #fff;
        }
        .form-top { display: flex; justify-content: flex-end; }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13.5px; font-weight: 600; color: var(--muted); text-decoration: none;
            padding: 8px 12px; border-radius: 10px; transition: background 0.2s, color 0.2s;
        }
        .back-link:hover { background: var(--field); color: var(--green-700); }
        .back-link svg { width: 16px; height: 16px; }

        .form-wrap {
            flex: 1;
            display: flex; flex-direction: column; justify-content: center;
            width: 100%; max-width: 400px; margin: 0 auto;
            padding: 24px 0;
            animation: rise 0.5s ease both;
        }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .mobile-brand { display: none; align-items: center; gap: 12px; margin-bottom: 28px; }
        .mobile-brand img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
        .mobile-brand .brand-name { color: var(--green-700); }
        .mobile-brand .brand-name span { color: var(--gold-dark); }

        .form-title { font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 700; letter-spacing: -0.6px; margin-bottom: 8px; }
        .form-subtitle { font-size: 14.5px; color: var(--muted); line-height: 1.55; margin-bottom: 30px; }

        .alert {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 12px 14px; border-radius: 12px; margin-bottom: 20px;
            font-size: 13.5px; font-weight: 500; line-height: 1.45;
        }
        .alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
        .alert-error { background: #FEF3F2; color: var(--danger); border: 1px solid #FECDCA; }
        .alert-success { background: #ECFDF3; color: var(--green-700); border: 1px solid #ABEFC6; }

        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 8px; color: #2E3D35; }
        .control { position: relative; }
        .control .lead {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: #8A9A91; pointer-events: none;
            transition: color 0.2s;
        }
        .control input {
            width: 100%; height: 50px;
            padding: 0 46px 0 45px;
            border: 1.5px solid var(--line); border-radius: 12px;
            background: var(--field);
            font: inherit; font-size: 15px; color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .control input::placeholder { color: #A3B1A9; }
        .control input:hover { border-color: #C4D5CB; }
        .control input:focus {
            outline: none; background: #fff;
            border-color: var(--green-500);
            box-shadow: 0 0 0 4px rgba(11, 170, 114, 0.13);
        }
        .control:focus-within .lead { color: var(--green-700); }
        .control input[aria-invalid="true"] { border-color: #F97066; }
        .toggle-pass {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 34px; height: 34px; border: 0; border-radius: 9px;
            background: transparent; color: #8A9A91; cursor: pointer;
            display: grid; place-items: center; transition: background 0.2s, color 0.2s;
        }
        .toggle-pass:hover { background: #EAF2EE; color: var(--green-700); }
        .toggle-pass:focus-visible { outline: 2px solid var(--green-500); outline-offset: 1px; }
        .toggle-pass svg { width: 18px; height: 18px; }

        .remember {
            display: inline-flex; align-items: center; gap: 9px;
            font-size: 13.5px; color: #44544B; font-weight: 500; cursor: pointer;
            margin: 4px 0 24px; user-select: none;
        }
        .remember input { width: 17px; height: 17px; accent-color: var(--green-700); cursor: pointer; }

        .btn-submit {
            width: 100%; height: 52px;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 12px;
            background: linear-gradient(135deg, var(--green-700), var(--green-800));
            color: #fff; font: inherit; font-size: 15.5px; font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(7, 91, 58, 0.55);
            transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 14px 28px -8px rgba(7, 91, 58, 0.6); filter: brightness(1.06); }
        .btn-submit:active { transform: none; }
        .btn-submit:focus-visible { outline: 3px solid rgba(11, 170, 114, 0.45); outline-offset: 2px; }
        .btn-submit svg { width: 18px; height: 18px; transition: transform 0.2s; }
        .btn-submit:hover svg { transform: translateX(3px); }
        .btn-submit[disabled] { opacity: 0.8; cursor: progress; transform: none; }
        .spinner {
            display: none; width: 18px; height: 18px; border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.35); border-top-color: #fff;
            animation: spin 0.7s linear infinite;
        }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading svg { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .register {
            margin-top: 28px; padding-top: 24px; border-top: 1px solid #EDF3EF;
            text-align: center; font-size: 14px; color: var(--muted);
        }
        .register a { color: var(--green-700); font-weight: 700; text-decoration: none; }
        .register a:hover { text-decoration: underline; }

        .form-foot { text-align: center; font-size: 12px; color: #9AA8A0; }

        @media (max-width: 960px) {
            .auth { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
            .form-panel { padding: 20px 16px; min-height: 100vh; }
            .mobile-brand { display: flex; }
            .form-title { font-size: 25px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>
    <div class="auth">
        <aside class="brand-panel" aria-hidden="true">
            <div class="brand-top">
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="">
                <div>
                    <div class="brand-name">Ecolácteos<span> Huata</span></div>
                    <div class="brand-tag">Sistema de Gestión y Acopio</div>
                </div>
            </div>

            <div class="brand-copy">
                <h1>Del productor a tu mesa, <span>con calidad garantizada.</span></h1>
                <p>Gestiona el acopio de leche, el control de calidad, la producción y las ventas desde un solo lugar.</p>
                <ul class="features">
                    <li>
                        <span class="tick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        Acopio y pagos a productores en tiempo real
                    </li>
                    <li>
                        <span class="tick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        Trazabilidad de calidad en cada entrega
                    </li>
                    <li>
                        <span class="tick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        Producción, inventario y pedidos en línea
                    </li>
                </ul>
            </div>

            <div class="brand-foot">© {{ date('Y') }} Ecolácteos Huata · by 4bytes</div>
        </aside>

        <main class="form-panel">
            <div class="form-top">
                <a href="{{ route('home') }}" class="back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Volver al inicio
                </a>
            </div>

            <div class="form-wrap">
                <div class="mobile-brand">
                    <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
                    <div class="brand-name">Ecolácteos<span> Huata</span></div>
                </div>

                <h2 class="form-title">Iniciar sesión</h2>
                <p class="form-subtitle">Ingresa con tu correo electrónico o DNI para acceder a tu cuenta.</p>

                @if(session('error'))
                    <div class="alert alert-error" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-error" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <div>
                            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('authenticate') }}" id="login-form" novalidate>
                    @csrf
                    <div class="field">
                        <label for="email">Correo electrónico o DNI</label>
                        <div class="control">
                            <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="nombre@correo.com o 12345678"
                                   autocomplete="username" required autofocus
                                   @error('email') aria-invalid="true" @enderror>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <div class="control">
                            <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña"
                                   autocomplete="current-password" required>
                            <button type="button" class="toggle-pass" id="toggle-pass" aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M1 1l22 22"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Mantener sesión iniciada
                    </label>

                    <button type="submit" class="btn-submit" id="submit-btn">
                        <span class="spinner" aria-hidden="true"></span>
                        <span class="label">Ingresar</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </form>

                <div class="register">
                    ¿Eres cliente y aún no tienes cuenta? <a href="{{ route('customer.register') }}">Crear cuenta</a>
                </div>
            </div>

            <div class="form-foot">Tus datos están protegidos. Nunca compartas tu contraseña.</div>
        </main>
    </div>

    <script>
        (function () {
            const toggle = document.getElementById('toggle-pass');
            const input = document.getElementById('password');
            toggle.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(show));
                toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
                toggle.querySelector('.eye-open').style.display = show ? 'none' : '';
                toggle.querySelector('.eye-closed').style.display = show ? '' : 'none';
            });

            const form = document.getElementById('login-form');
            const button = document.getElementById('submit-btn');
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    form.reportValidity();
                    return;
                }
                button.disabled = true;
                button.classList.add('loading');
                button.querySelector('.label').textContent = 'Ingresando...';
            });
        })();
    </script>
</body>
</html>
