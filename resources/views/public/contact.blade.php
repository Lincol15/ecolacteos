<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 70px;">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">📩 Hablemos</div>
            <h1>Contáctanos</h1>
            <p style="margin:0 auto;">¿Tienes una consulta, quieres hacer un pedido o ser proveedor? Escríbenos y te responderemos a la brevedad.</p>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,64 C240,10 480,10 720,40 C960,70 1200,70 1440,32 L1440,90 L0,90 Z" fill="#F6FBF8"></path>
        </svg>
    </div>
</section>

<section style="padding-top:60px;">
    <div class="container" style="max-width:1000px;">
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:40px;align-items:start;">
            <div class="form-card reveal is-visible">
                @if(session('success'))
                    <div class="alert alert-success">✅ {{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-error">
                        ❌
                        <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}">
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
                        <label class="form-label">Asunto</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-input" placeholder="¿En qué te podemos ayudar?">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mensaje *</label>
                        <textarea name="message" required class="form-textarea" placeholder="Cuéntanos más detalles...">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">Enviar Mensaje</button>
                </form>
            </div>

            <div>
                <div class="benefit-card reveal" style="margin-bottom:20px;">
                    <div class="benefit-icon green">📍</div>
                    <h3>Ubicación</h3>
                    <p>Av. Principal S/N, Huata, Ancash, Perú</p>
                </div>
                <div class="benefit-card reveal reveal-delay-1" style="margin-bottom:20px;">
                    <div class="benefit-icon amber">📞</div>
                    <h3>Teléfono</h3>
                    <p>+51 43 123456</p>
                </div>
                <div class="benefit-card reveal reveal-delay-2">
                    <div class="benefit-icon blue">✉️</div>
                    <h3>Email</h3>
                    <p>info@ecolacteoshuata.com</p>
                </div>
            </div>
        </div>
    </div>
</section>

@include('public.partials.footer')
</body>
</html>
