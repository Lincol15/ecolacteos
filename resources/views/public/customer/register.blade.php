<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 70px;">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">👤 Nueva Cuenta</div>
            <h1>Crea tu <span>Cuenta de Cliente</span></h1>
            <p style="margin:0 auto;">Regístrate para agilizar tus próximas compras: guarda tus datos y revisa el historial de tus pedidos.</p>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,64 C240,10 480,10 720,40 C960,70 1200,70 1440,32 L1440,90 L0,90 Z" fill="#F6FBF8"></path>
        </svg>
    </div>
</section>

<section style="padding-top:20px;">
    <div class="container" style="max-width:520px;">
        <div class="form-card reveal is-visible">
            @if($errors->any())
                <div class="alert alert-error">
                    ❌
                    <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.register.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Tu nombre">
                </div>
                <div class="form-group">
                    <label class="form-label">Correo electrónico *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+51 987 654 321">
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="form-input" placeholder="Para tus entregas">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" required class="form-input" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">Crear Cuenta</button>
            </form>
            <p style="margin-top:18px;text-align:center;font-size:13.5px;color:var(--c-text-light);">
                ¿Ya tienes cuenta? <a href="{{ route('customer.login') }}" class="link" style="color:var(--c-green-dark);font-weight:700;">Inicia sesión</a>
            </p>
        </div>
    </div>
</section>

@include('public.partials.footer')
</body>
</html>
