<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VACA SYS - Leche Fresca y Productos Lácteos de Calidad</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.06);
        }
        .nav-inner {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 0;
        }
        .logo {
            display: flex; align-items: center; gap: 10px;
            font-weight: 900; font-size: 22px; letter-spacing: -0.5px;
        }
        .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 22px; box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .logo span { color: #10b981; }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a { font-weight: 500; font-size: 14.5px; color: #475569; transition: color 0.2s; }
        .nav-links a:hover { color: #059669; }
        .nav-cta { display: flex; gap: 10px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: 10px; font-weight: 600; font-size: 14px;
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.3); }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-outline { background: #fff; color: #059669; border: 2px solid #10b981; }
        .btn-accent { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 14px rgba(245,158,11,0.3); }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #10b981 0%, #34d399 30%, #fbbf24 70%, #f59e0b 100%);
            padding: 160px 0 120px; position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.2), transparent);
            border-radius: 50%;
        }
        .hero::after {
            content: ''; position: absolute; bottom: -150px; left: -150px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.15), transparent);
            border-radius: 50%;
        }
        .hero-inner {
            display: grid; grid-template-columns: 1.2fr 1fr; gap: 60px;
            align-items: center; position: relative; z-index: 2;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.25); padding: 8px 16px; border-radius: 50px;
            color: #fff; font-size: 13px; font-weight: 600;
            margin-bottom: 20px; backdrop-filter: blur(4px);
        }
        .hero h1 {
            font-size: 56px; font-weight: 900; line-height: 1.08;
            color: #fff; letter-spacing: -1.5px; margin-bottom: 20px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .hero h1 span {
            background: linear-gradient(135deg, #fff9c4, #fff);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 18px; color: rgba(255,255,255,0.9); margin-bottom: 32px;
            max-width: 520px;
        }
        .hero-cta { display: flex; gap: 14px; flex-wrap: wrap; }
        .hero-stats {
            display: flex; gap: 40px; margin-top: 48px; flex-wrap: wrap;
        }
        .hero-stat h3 { font-size: 32px; font-weight: 900; color: #fff; }
        .hero-stat p { font-size: 13px; color: rgba(255,255,255,0.8); margin: 0; }

        .hero-visual {
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .hero-card {
            background: #fff; border-radius: 28px; padding: 30px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            max-width: 380px; width: 100%;
        }
        .hero-card-img {
            width: 100%; height: 220px; border-radius: 18px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex; align-items: center; justify-content: center;
            font-size: 120px; margin-bottom: 20px;
        }
        .hero-card-title { font-size: 20px; font-weight: 800; margin-bottom: 6px; }
        .hero-card-price {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 0; border-top: 1px solid #f1f5f9;
        }
        .hero-card-price strong {
            font-size: 28px; font-weight: 900;
            background: linear-gradient(135deg, #059669, #10b981);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .rating { color: #f59e0b; font-weight: 600; }

        /* SECTIONS */
        section { padding: 100px 0; }
        .section-header { text-align: center; max-width: 700px; margin: 0 auto 60px; }
        .section-badge {
            display: inline-block; background: #d1fae5; color: #059669;
            padding: 6px 16px; border-radius: 50px; font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;
        }
        .section-title {
            font-size: 42px; font-weight: 900; letter-spacing: -1px;
            line-height: 1.15; margin-bottom: 14px;
        }
        .section-title span {
            background: linear-gradient(135deg, #059669, #10b981);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .section-subtitle { font-size: 17px; color: #64748b; }

        /* BENEFICIOS */
        .benefits { background: #f8fafc; }
        .benefits-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;
        }
        .benefit-card {
            background: #fff; border-radius: 20px; padding: 32px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.3s; border: 1px solid rgba(255,255,255,0.8);
        }
        .benefit-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
        .benefit-icon {
            width: 56px; height: 56px; border-radius: 14px; margin-bottom: 18px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .benefit-icon.green { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
        .benefit-icon.amber { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .benefit-icon.blue { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .benefit-icon.purple { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }
        .benefit-card h3 { font-size: 19px; font-weight: 800; margin-bottom: 10px; }
        .benefit-card p { color: #64748b; font-size: 14.5px; }

        /* PRODUCTOS DESTACADOS */
        .products-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
        }
        .product-card {
            background: #fff; border-radius: 22px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s; border: 1px solid rgba(255,255,255,0.8);
        }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.12); }
        .product-img {
            height: 220px;
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center;
            font-size: 100px;
        }
        .product-body { padding: 24px; }
        .product-cat {
            font-size: 11px; font-weight: 700; color: #059669;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
        }
        .product-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .product-card p { color: #64748b; font-size: 13.5px; margin-bottom: 16px; min-height: 42px; }
        .product-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 16px; border-top: 1px solid #f1f5f9;
        }
        .product-price {
            font-size: 24px; font-weight: 900;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stock-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
        }
        .stock-badge.ok { background: #d1fae5; color: #065f46; }
        .stock-badge.low { background: #fef3c7; color: #92400e; }

        /* TESTIMONIOS */
        .testimonials {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: #fff;
        }
        .testimonials .section-badge { background: rgba(255,255,255,0.2); color: #fff; }
        .testimonials .section-title { color: #fff; }
        .testimonials .section-title span {
            background: linear-gradient(135deg, #fbbf24, #fff9c4);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .testimonials .section-subtitle { color: rgba(255,255,255,0.7); }
        .testimonials-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
        }
        .testimonial-card {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px; padding: 28px;
        }
        .testimonial-stars { color: #fbbf24; font-size: 16px; margin-bottom: 14px; }
        .testimonial-text {
            font-size: 15px; line-height: 1.7; margin-bottom: 22px;
            color: rgba(255,255,255,0.9);
        }
        .testimonial-author { display: flex; align-items: center; gap: 12px; }
        .testimonial-avatar {
            width: 46px; height: 46px; border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #065f46;
        }
        .testimonial-name { font-weight: 700; font-size: 14.5px; }
        .testimonial-role { font-size: 12px; color: rgba(255,255,255,0.6); }

        /* CTA CONTACTO */
        .cta-section { padding: 80px 0; }
        .cta-box {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #10b981 100%);
            border-radius: 30px; padding: 60px;
            display: grid; grid-template-columns: 1.3fr 1fr; gap: 40px;
            align-items: center; position: relative; overflow: hidden;
        }
        .cta-box::before {
            content: ''; position: absolute; top: -100px; right: -100px;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.15); border-radius: 50%;
        }
        .cta-content { position: relative; z-index: 2; }
        .cta-content h2 {
            font-size: 38px; font-weight: 900; color: #fff;
            letter-spacing: -1px; line-height: 1.15; margin-bottom: 14px;
        }
        .cta-content p { font-size: 17px; color: rgba(255,255,255,0.9); margin-bottom: 28px; }
        .cta-buttons { display: flex; gap: 14px; flex-wrap: wrap; }
        .cta-buttons .btn { font-size: 15px; padding: 14px 28px; }
        .btn-white { background: #fff; color: #059669; }
        .btn-white:hover { background: #f8fafc; }
        .btn-dark { background: #065f46; color: #fff; }

        /* FOOTER */
        footer {
            background: #0f172a; color: #94a3b8; padding: 70px 0 30px;
        }
        .footer-grid {
            display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 40px;
            margin-bottom: 50px;
        }
        .footer-logo {
            display: flex; align-items: center; gap: 10px;
            font-weight: 900; font-size: 22px; color: #fff; margin-bottom: 14px;
        }
        .footer-desc { font-size: 14px; line-height: 1.7; margin-bottom: 20px; }
        .footer-socials { display: flex; gap: 10px; }
        .social-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(255,255,255,0.08);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .social-icon:hover { background: #10b981; color: #fff; }
        .footer-col h4 {
            color: #fff; font-weight: 800; font-size: 15px; margin-bottom: 18px;
        }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { font-size: 14px; transition: color 0.2s; }
        .footer-col ul li a:hover { color: #10b981; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 24px;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .hero-inner, .cta-box { grid-template-columns: 1fr; }
            .benefits-grid, .products-grid, .testimonials-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .hero h1 { font-size: 38px; }
            .section-title { font-size: 32px; }
            .nav-links { display: none; }
            .hero { padding: 130px 0 80px; }
            section { padding: 70px 0; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container nav-inner">
        <a href="{{ route('home') }}" class="logo">
            <div class="logo-icon">🥛</div>
            <div>VACA<span>SYS</span></div>
        </a>
        <div class="nav-links">
            <a href="{{ route('home') }}">Inicio</a>
            <a href="{{ route('about') }}">Nosotros</a>
            <a href="{{ route('catalog') }}">Productos</a>
            <a href="{{ route('contact') }}">Contacto</a>
        </div>
        <div class="nav-cta">
            <a href="{{ route('login') }}" class="btn btn-outline">Iniciar Sesión</a>
            <a href="{{ route('catalog') }}" class="btn btn-primary">Ver Productos</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container hero-inner">
        <div>
            <div class="hero-badge">✨ 100% Leche Fresca y Natural</div>
            <h1>Leche de <span>Calidad Premium</span><br>Directo del Campo a Tu Mesa</h1>
            <p>Productos lácteos artesanales elaborados con leche fresca de productores locales. Control de calidad certificado en cada entrega.</p>
            <div class="hero-cta">
                <a href="{{ route('catalog') }}" class="btn btn-primary">🛒 Comprar Ahora</a>
                <a href="{{ route('about') }}" class="btn btn-white" style="background:#fff;color:#059669">Conocer Más →</a>
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
            <div class="hero-card">
                <div class="hero-card-img">🧀</div>
                <div class="hero-card-title">Queso Fresco Artesanal</div>
                <p style="font-size:13px;color:#64748b">Elaborado con leche fresca de pastoreo</p>
                <div class="hero-card-price">
                    <strong>S/ 32.00</strong>
                    <span class="rating">⭐⭐⭐⭐⭐ 4.9</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BENEFICIOS -->
<section class="benefits">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Por qué elegirnos</span>
            <h2 class="section-title">Los Beneficios de <span>VACA SYS</span></h2>
            <p class="section-subtitle">Trabajamos con pasión para ofrecerte los mejores productos lácteos, cuidando cada detalle del proceso.</p>
        </div>
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon green">🌿</div>
                <h3>100% Natural</h3>
                <p>Sin conservantes ni aditivos artificiales. Producto puro directamente del campo.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon amber">🧪</div>
                <h3>Control de Calidad</h3>
                <p>Cada entrega pasa por análisis LACTOMAT certificado para garantizar frescura.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon blue">👨‍🌾</div>
                <h3>Productores Locales</h3>
                <p>Apoyamos a pequeños ganaderos con pago justo y relaciones duraderas.</p>
            </div>
            <div class="benefit-card">
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
        <div class="section-header">
            <span class="section-badge">Nuestros productos</span>
            <h2 class="section-title">Productos <span>Destacados</span></h2>
            <p class="section-subtitle">Explora nuestra selección de productos lácteos frescos y artesanales.</p>
        </div>
        <div class="products-grid">
            @forelse($featuredProducts ?? [] as $p)
            <div class="product-card">
                <div class="product-img" style="background-image:url('{{ $p->image ?? ('https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=' . urlencode($p->name ?? 'Queso fresco artesanal empaque') . '&image_size=landscape_4_3') }}')">
                    {{ $p->icon ?? '🧀' }}
                </div>
                <div class="product-body">
                    <div class="product-cat">{{ $p->category ?? 'Lácteos' }}</div>
                    <h3>{{ $p->name ?? 'Producto' }}</h3>
                    <p>{{ $p->description ?? 'Producto lácteo fresco y de calidad premium.' }}</p>
                    <div class="product-footer">
                        <span class="product-price">S/ {{ number_format($p->price ?? 0, 2) }}</span>
                        <span class="stock-badge {{ ($p->stock ?? 0) > 10 ? 'ok' : 'low' }}">{{ $p->stock ?? 0 }} disp.</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="product-card">
                <div class="product-img" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0)">🥛</div>
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
            <div class="product-card">
                <div class="product-img" style="background:linear-gradient(135deg,#fef3c7,#fde68a)">🧀</div>
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
            <div class="product-card">
                <div class="product-img" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)">🍦</div>
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
        <div class="section-header">
            <span class="section-badge">Testimonios</span>
            <h2 class="section-title">Lo que Dicen <span>Nuestros Clientes</span></h2>
            <p class="section-subtitle">Miles de familias confían en la calidad y frescura de nuestros productos lácteos.</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"La leche de VACA SYS es increíble. Se nota la frescura desde el primer sorbo. Mi familia la consume todos los días."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MR</div>
                    <div>
                        <div class="testimonial-name">María Rodríguez</div>
                        <div class="testimonial-role">Cliente desde 2023</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"Excelente queso fresco. En mi pastelería usamos productos VACA SYS y nuestros clientes lo notan. 100% recomendado."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff">JT</div>
                    <div>
                        <div class="testimonial-name">Juan Torres</div>
                        <div class="testimonial-role">Chef Pastelero</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                <p class="testimonial-text">"Como productor, valoro que paguen justo y el trato transparente. El sistema de control de calidad es excelente."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff">PC</div>
                    <div>
                        <div class="testimonial-name">Pedro Castillo</div>
                        <div class="testimonial-role">Ganadero Asociado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-box">
            <div class="cta-content">
                <h2>¿Listo para probar la<br>mejor leche fresca?</h2>
                <p>Contáctanos y recibe atención personalizada. Haz tu pedido o resuelve cualquier duda que tengas.</p>
                <div class="cta-buttons">
                    <a href="{{ route('contact') }}" class="btn btn-white">📩 Contáctanos</a>
                    <a href="{{ route('catalog') }}" class="btn btn-dark">🛒 Ir al Catálogo</a>
                </div>
            </div>
            <div style="text-align:center;position:relative;z-index:2">
                <div style="font-size:160px">📞</div>
                <div style="color:#fff;font-weight:900;font-size:24px;margin-top:-20px">+51 987 654 321</div>
                <div style="color:rgba(255,255,255,0.8);font-size:14px">Atención Lunes a Sábado</div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo">
                    <div class="logo-icon">🥛</div>
                    <div>VACA<span style="color:#10b981">SYS</span></div>
                </div>
                <p class="footer-desc">Sistema de gestión y comercialización de productos lácteos frescos. Calidad que se percibe en cada gota.</p>
                <div class="footer-socials">
                    <a href="#" class="social-icon">📘</a>
                    <a href="#" class="social-icon">📷</a>
                    <a href="#" class="social-icon">🐦</a>
                    <a href="#" class="social-icon">📱</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Empresa</h4>
                <ul>
                    <li><a href="{{ route('about') }}">Nosotros</a></li>
                    <li><a href="{{ route('catalog') }}">Productos</a></li>
                    <li><a href="{{ route('contact') }}">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Soporte</h4>
                <ul>
                    <li><a href="#">Preguntas Frecuentes</a></li>
                    <li><a href="#">Envíos</a></li>
                    <li><a href="#">Devoluciones</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contacto</h4>
                <ul>
                    <li>📍 Av. Principal 123, Lima</li>
                    <li>📞 +51 987 654 321</li>
                    <li>✉️ info@vacasys.pe</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>© {{ date('Y') }} VACA SYS. Todos los derechos reservados.</div>
            <div style="display:flex;gap:24px">
                <a href="#">Política de Privacidad</a>
                <a href="#">Términos y Condiciones</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
