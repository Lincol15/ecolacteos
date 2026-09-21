<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 70px;">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">🌿 Nuestra Historia</div>
            <h1>Del Campo de <span>Huata</span><br>a tu Mesa</h1>
            <p style="margin:0 auto 0;max-width:640px;">Somos una empresa familiar que trabaja de la mano con productores locales de la región de Áncash para llevar leche fresca y productos lácteos artesanales de la más alta calidad a cada hogar.</p>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,64 C240,10 480,10 720,40 C960,70 1200,70 1440,32 L1440,90 L0,90 Z" fill="#F6FBF8"></path>
        </svg>
    </div>
</section>

<section>
    <div class="container">
        <div class="split">
            <div class="split-media reveal">
                <div class="split-media-dot"></div>
                <img src="{{ asset('images/planta-produccion.jpg') }}" alt="Elaboración artesanal de queso en la planta de Ecolácteos Huata">
                <div class="split-media-badge">
                    <div class="smb-icon">🧀</div>
                    <div>
                        <strong>100%</strong>
                        <span>Elaboración Artesanal</span>
                    </div>
                </div>
            </div>
            <div class="split-text reveal reveal-delay-1">
                <span class="section-badge">Nuestra misión</span>
                <h2>Calidad que nace en el <span style="color:var(--c-gold-dark)">campo</span></h2>
                <p>Desde nuestra planta en Huata, Áncash, recolectamos, analizamos y procesamos leche fresca cada día, garantizando trazabilidad completa desde el productor hasta el consumidor final.</p>
                <div class="split-points">
                    <div class="split-point">
                        <div class="split-point-icon">🐄</div>
                        <div>
                            <strong>Productores Aliados</strong>
                            <span>Trabajamos con decenas de familias ganaderas de la zona, pagando precios justos por cada litro entregado.</span>
                        </div>
                    </div>
                    <div class="split-point">
                        <div class="split-point-icon">🧪</div>
                        <div>
                            <strong>Control LACTOMAT</strong>
                            <span>Cada entrega pasa por un riguroso análisis de calidad antes de ingresar a producción.</span>
                        </div>
                    </div>
                    <div class="split-point">
                        <div class="split-point-icon">🌱</div>
                        <div>
                            <strong>Compromiso Local</strong>
                            <span>Impulsamos el desarrollo económico de la comunidad de Huata y sus alrededores.</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('catalog') }}" class="btn btn-primary">Ver Nuestros Productos</a>
            </div>
        </div>
    </div>
</section>

<section class="benefits">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge">Lo que nos distingue</span>
            <h2 class="section-title">Compromiso en <span>cada etapa</span></h2>
            <p class="section-subtitle">Del productor al consumidor, cuidamos cada detalle del proceso lácteo.</p>
        </div>
        <div class="benefits-grid">
            <div class="benefit-card reveal">
                <div class="benefit-icon green">🐄</div>
                <h3>Productores Aliados</h3>
                <p>Trabajamos con decenas de familias ganaderas de la zona, pagando precios justos por cada litro entregado.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-1">
                <div class="benefit-icon amber">🧪</div>
                <h3>Control LACTOMAT</h3>
                <p>Cada entrega pasa por un riguroso análisis de calidad antes de ingresar a producción.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-2">
                <div class="benefit-icon blue">🏭</div>
                <h3>Planta Propia</h3>
                <p>Procesamos quesos, yogures y mantequilla artesanal bajo estrictos estándares de higiene.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-3">
                <div class="benefit-icon purple">🌱</div>
                <h3>Compromiso Local</h3>
                <p>Impulsamos el desarrollo económico de la comunidad de Huata y sus alrededores.</p>
            </div>
        </div>
    </div>
</section>

<section class="stats-band">
    <div class="container stats-band-grid">
        <div class="stat-box reveal">
            <div class="stat-icon">🐄</div>
            <h3>{{ $stats['producers'] ?? '—' }}</h3>
            <p>Productores Activos</p>
        </div>
        <div class="stat-box reveal reveal-delay-1">
            <div class="stat-icon">🧀</div>
            <h3>{{ $stats['products'] ?? '—' }}</h3>
            <p>Productos en Catálogo</p>
        </div>
        <div class="stat-box reveal reveal-delay-2">
            <div class="stat-icon">🥛</div>
            <h3>S/ {{ number_format($stats['price'] ?? 1.70, 2) }}</h3>
            <p>Precio por Litro</p>
        </div>
        <div class="stat-box reveal reveal-delay-3">
            <div class="stat-icon">📍</div>
            <h3>Huata</h3>
            <p>Áncash, Perú</p>
        </div>
    </div>
</section>

@include('public.partials.cta')
@include('public.partials.footer')
</body>
</html>
