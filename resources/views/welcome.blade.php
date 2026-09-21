<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecolácteos Huata - Leche Fresca y Productos Lácteos de Calidad</title>
    @include('public.partials.styles')
</head>
<body>

@include('public.partials.navbar')

<!-- HERO -->
<section class="hero">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="hero-blob-3"></div>
    <div class="container hero-inner">
        <div>
            <div class="hero-badge">🌿 100% Leche Fresca y Natural</div>
            <h1>Leche de Calidad<br>Directo del Campo<br>a <span>Tu Mesa</span></h1>
            <p>Productos lácteos elaborados con leche fresca de productores locales de Huata. Control de calidad certificado en cada entrega.</p>
            <div class="hero-cta">
                <a href="{{ route('catalog') }}" class="btn btn-primary">Comprar Ahora</a>
                <a href="{{ route('about') }}" class="btn btn-outline">Conocer Más</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <h3>+15K</h3>
                    <p>Litros al Mes</p>
                </div>
                <div class="hero-stat">
                    <h3>+200</h3>
                    <p>Clientes Felices</p>
                </div>
                <div class="hero-stat">
                    <h3>98%</h3>
                    <p>Calidad Aprobada</p>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-visual-ring">
                <div class="hero-gold-chip">🐄 Del campo de Huata</div>
                <img src="{{ asset('images/logo-ecolacteos.png') }}" alt="Ecolácteos Huata">
                <div class="hero-card">
                    <div class="hero-card-img">🧀</div>
                    <div class="hero-card-title">Queso Fresco Artesanal</div>
                    <p style="font-size:12.5px;color:var(--c-text-light)">Elaborado con leche de pastoreo</p>
                    <div class="hero-card-price">
                        <strong>S/ 32.00</strong>
                        <span class="rating">⭐⭐⭐⭐⭐ 4.9</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,64 C240,10 480,10 720,40 C960,70 1200,70 1440,32 L1440,90 L0,90 Z" fill="#F6FBF8"></path>
        </svg>
    </div>
</section>

<!-- ESTADÍSTICAS -->
<section class="stats-band">
    <div class="container stats-band-grid">
        <div class="stat-box reveal">
            <div class="stat-icon">🥛</div>
            <h3>+15K</h3>
            <p>Litros al Mes</p>
        </div>
        <div class="stat-box reveal reveal-delay-1">
            <div class="stat-icon">👨‍👩‍👧</div>
            <h3>+200</h3>
            <p>Clientes Felices</p>
        </div>
        <div class="stat-box reveal reveal-delay-2">
            <div class="stat-icon">🐄</div>
            <h3>{{ $stats['producers'] ?? 50 }}</h3>
            <p>Productores Aliados</p>
        </div>
        <div class="stat-box reveal reveal-delay-3">
            <div class="stat-icon">✅</div>
            <h3>98%</h3>
            <p>Calidad Aprobada</p>
        </div>
    </div>
</section>

<!-- BENEFICIOS -->
<section class="benefits">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge">Por qué elegirnos</span>
            <h2 class="section-title">Los Beneficios de <span>Ecolácteos Huata</span></h2>
            <p class="section-subtitle">Trabajamos con pasión para ofrecerte los mejores productos lácteos, cuidando cada detalle del proceso.</p>
        </div>
        <div class="benefits-grid">
            <div class="benefit-card reveal">
                <div class="benefit-icon green">🌿</div>
                <h3>100% Natural</h3>
                <p>Sin conservantes ni aditivos artificiales. Producto puro directamente del campo.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-1">
                <div class="benefit-icon amber">🧪</div>
                <h3>Control de Calidad</h3>
                <p>Cada entrega pasa por análisis LACTOMAT certificado para garantizar frescura.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-2">
                <div class="benefit-icon blue">👨‍🌾</div>
                <h3>Productores Locales</h3>
                <p>Apoyamos a pequeños ganaderos con pago justo y relaciones duraderas.</p>
            </div>
            <div class="benefit-card reveal reveal-delay-3">
                <div class="benefit-icon purple">🚚</div>
                <h3>Entrega Rápida</h3>
                <p>Logística refrigerada para que tu leche llegue fresca a su destino.</p>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section>
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge">Nuestros productos</span>
            <h2 class="section-title">Productos <span>Destacados</span></h2>
            <p class="section-subtitle">Explora nuestra selección de productos lácteos frescos y artesanales.</p>
        </div>
        <div class="products-grid">
            @forelse($featuredProducts ?? [] as $p)
            <a href="{{ route('product.show', $p->slug) }}" class="product-card reveal reveal-delay-{{ $loop->iteration }}">
                <div class="product-img" style="background-image:url('{{ $p->image_url ?? '' }}')">
                    @unless($p->image_url) {{ $p->emoji ?? '🧀' }} @endunless
                </div>
                <div class="product-body">
                    <div class="product-cat">{{ $p->category ? (\App\Models\Product::CATEGORIES[$p->category] ?? $p->category) : 'Lácteos' }}</div>
                    <h3>{{ $p->name ?? 'Producto' }}</h3>
                    <p>{{ $p->description ?? 'Producto lácteo fresco y de calidad premium.' }}</p>
                    <div class="product-footer">
                        <span class="product-price">S/ {{ number_format($p->unit_price ?? 0, 2) }}</span>
                        <span class="stock-badge {{ $p->currentStock() > 10 ? 'ok' : 'low' }}">{{ number_format($p->currentStock(), 0) }} disp.</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="product-card reveal">
                <div class="product-img" style="background:var(--c-green-light)">🥛</div>
                <div class="product-body">
                    <div class="product-cat">Leche Fresca</div>
                    <h3>Leche Entera Pasteurizada</h3>
                    <p>Leche fresca entera pasteurizada, lista para consumo diario.</p>
                    <div class="product-footer">
                        <span class="product-price">S/ 4.80</span>
                        <span class="stock-badge ok">150 disp.</span>
                    </div>
                </div>
            </div>
            <div class="product-card reveal reveal-delay-1">
                <div class="product-img" style="background:#FBF0D3">🧀</div>
                <div class="product-body">
                    <div class="product-cat">Quesos</div>
                    <h3>Queso Fresco Artesanal</h3>
                    <p>Queso fresco blando elaborado artesanalmente con leche cruda.</p>
                    <div class="product-footer">
                        <span class="product-price">S/ 32.00</span>
                        <span class="stock-badge ok">45 disp.</span>
                    </div>
                </div>
            </div>
            <div class="product-card reveal reveal-delay-2">
                <div class="product-img" style="background:#ECE7FA">🍦</div>
                <div class="product-body">
                    <div class="product-cat">Yogures</div>
                    <h3>Yogurt Griego Natural</h3>
                    <p>Yogurt griego cremoso, alto en proteínas y probióticos naturales.</p>
                    <div class="product-footer">
                        <span class="product-price">S/ 8.50</span>
                        <span class="stock-badge low">8 disp.</span>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="{{ route('catalog') }}" class="btn btn-primary" style="font-size:15px;padding:14px 32px">
                Ver Catálogo Completo →
            </a>
        </div>
    </div>
</section>

<!-- TESTIMONIOS -->
<section class="testimonials">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-badge">Testimonios</span>
            <h2 class="section-title">Lo que Dicen <span>Nuestros Clientes</span></h2>
            <p class="section-subtitle">Miles de familias confían en la calidad y frescura de nuestros productos lácteos.</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card reveal">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"La leche de Ecolácteos Huata es increíble. Se nota la frescura desde el primer sorbo. Mi familia la consume todos los días."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MR</div>
                    <div>
                        <div class="testimonial-name">María Rodríguez</div>
                        <div class="testimonial-role">Cliente desde 2023</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card reveal reveal-delay-1">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"Excelente queso fresco. En mi pastelería usamos productos Ecolácteos Huata y nuestros clientes lo notan. 100% recomendado."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar" style="background:var(--c-gold);color:var(--c-green-dark)">JT</div>
                    <div>
                        <div class="testimonial-name">Juan Torres</div>
                        <div class="testimonial-role">Chef Pastelero</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card reveal reveal-delay-2">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"Como productor, valoro que paguen justo y el trato transparente. El sistema de control de calidad es excelente."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar" style="background:var(--c-green);color:#fff">PC</div>
                    <div>
                        <div class="testimonial-name">Pedro Castillo</div>
                        <div class="testimonial-role">Ganadero Asociado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('public.partials.cta')
@include('public.partials.footer')

</body>
</html>
