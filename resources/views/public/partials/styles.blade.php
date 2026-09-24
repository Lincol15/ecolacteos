<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/logo-ecolacteos.png') }}">
<style>
    :root {
        --c-green-dark: #075B3A;
        --c-green: #0BAA72;
        --c-green-light: #DDF5E9;
        --c-gold: #F2C94C;
        --c-gold-dark: #D6A927;
        --c-white: #FFFFFF;
        --c-text: #24332D;
        --c-text-light: #5C6E64;
        --c-border: #E3EEE8;
        --c-bg-soft: #F6FBF8;
        --shadow-sm: 0 2px 10px rgba(7,91,58,0.06);
        --shadow-md: 0 10px 30px rgba(7,91,58,0.09);
        --shadow-lg: 0 20px 50px rgba(7,91,58,0.14);
        --radius: 20px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--c-text);
        line-height: 1.65;
        background: var(--c-white);
        -webkit-font-smoothing: antialiased;
    }
    h1, h2, h3, h4 { font-family: 'Poppins', 'Inter', sans-serif; color: var(--c-green-dark); }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* ---------- REVEAL ANIMATIONS ---------- */
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
    .reveal.is-visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .08s; }
    .reveal-delay-2 { transition-delay: .16s; }
    .reveal-delay-3 { transition-delay: .24s; }
    .reveal-delay-4 { transition-delay: .32s; }

    /* ---------- NAVBAR ---------- */
    .navbar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--c-border);
        box-shadow: 0 1px 0 rgba(7,91,58,0.03);
        transition: box-shadow .25s ease;
    }
    .nav-inner { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; gap: 20px; }
    .logo { display: flex; align-items: center; gap: 12px; }
    .logo-icon {
        width: 48px; height: 48px; border-radius: 50%;
        overflow: hidden; flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(7,91,58,0.18);
        border: 2px solid var(--c-white);
    }
    .logo-icon img { width: 100%; height: 100%; object-fit: cover; }
    .logo-wordmark { display: flex; flex-direction: column; line-height: 1.05; }
    .logo-wordmark .lw-main { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 18px; color: var(--c-green-dark); letter-spacing: -0.3px; }
    .logo-wordmark .lw-sub { font-size: 10.5px; font-weight: 700; letter-spacing: 2.5px; color: var(--c-gold-dark); text-transform: uppercase; }
    .nav-links { display: flex; gap: 34px; align-items: center; }
    .nav-links a { position: relative; font-weight: 600; font-size: 14.5px; color: var(--c-text); padding: 6px 2px; transition: color .2s; }
    .nav-links a::after {
        content: ''; position: absolute; left: 0; right: 100%; bottom: 0; height: 2px;
        background: var(--c-gold); transition: right .25s ease;
    }
    .nav-links a:hover, .nav-links a.active { color: var(--c-green-dark); }
    .nav-links a:hover::after, .nav-links a.active::after { right: 0; }
    .nav-cta { display: flex; gap: 10px; }

    /* ---------- BUTTONS ---------- */
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 24px; border-radius: 12px; font-weight: 600; font-size: 14px;
        border: 1.5px solid transparent; cursor: pointer; transition: all .2s ease;
        white-space: nowrap;
    }
    .btn:hover { transform: translateY(-2px); }
    .btn:active { transform: translateY(0); }
    .btn-primary { background: var(--c-green-dark); color: var(--c-white); box-shadow: 0 6px 18px rgba(7,91,58,0.28); }
    .btn-primary:hover { background: #06492f; box-shadow: 0 8px 22px rgba(7,91,58,0.36); }
    .btn-outline { background: var(--c-white); color: var(--c-green-dark); border-color: var(--c-green); }
    .btn-outline:hover { background: var(--c-green-light); }
    .btn-accent { background: var(--c-gold); color: var(--c-green-dark); box-shadow: 0 6px 18px rgba(242,201,76,0.35); }
    .btn-accent:hover { background: var(--c-gold-dark); }
    .btn-white { background: var(--c-white); color: var(--c-green-dark); }
    .btn-white:hover { background: var(--c-green-light); }
    .btn-dark { background: var(--c-green-dark); color: var(--c-white); }
    .btn-dark:hover { background: #06492f; }

    /* ---------- HERO ---------- */
    .hero {
        position: relative; overflow: hidden;
        padding: 168px 0 60px;
        background: linear-gradient(180deg, var(--c-bg-soft) 0%, var(--c-white) 100%);
    }
    .hero-blob-1, .hero-blob-2, .hero-blob-3 { position: absolute; border-radius: 50%; z-index: 0; }
    .hero-blob-1 { width: 560px; height: 560px; top: -220px; right: -160px; background: radial-gradient(circle at 30% 30%, var(--c-green-light), transparent 70%); }
    .hero-blob-2 { width: 320px; height: 320px; bottom: -140px; left: -100px; background: radial-gradient(circle at 60% 40%, rgba(242,201,76,0.22), transparent 70%); }
    .hero-blob-3 { width: 180px; height: 180px; top: 40%; left: 46%; background: radial-gradient(circle, rgba(11,170,114,0.10), transparent 70%); }
    .hero-wave { position: absolute; left: 0; right: 0; bottom: -1px; z-index: 1; line-height: 0; }
    .hero-wave svg { width: 100%; height: auto; display: block; }
    .hero-inner {
        display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px;
        align-items: center; position: relative; z-index: 2;
    }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--c-green-light); color: var(--c-green-dark);
        padding: 8px 18px; border-radius: 50px;
        font-size: 12.5px; font-weight: 700; letter-spacing: .3px;
        margin-bottom: 22px; border: 1px solid rgba(7,91,58,0.10);
    }
    .hero h1 {
        font-size: 50px; font-weight: 900; line-height: 1.12;
        letter-spacing: -1.3px; margin-bottom: 20px; color: var(--c-green-dark);
    }
    .hero h1 span {
        background: linear-gradient(120deg, var(--c-gold-dark), var(--c-gold));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero p { font-size: 17.5px; color: var(--c-text-light); margin-bottom: 34px; max-width: 500px; }
    .hero-cta { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-stats { display: flex; gap: 44px; margin-top: 40px; flex-wrap: wrap; }
    .hero-stat h3 { font-size: 30px; font-weight: 900; color: var(--c-green-dark); }
    .hero-stat p { font-size: 13px; color: var(--c-text-light); margin: 2px 0 0; font-weight: 500; }

    .hero-visual { position: relative; display: flex; align-items: center; justify-content: center; }
    .hero-visual-ring {
        width: 380px; height: 380px; border-radius: 50%;
        background: linear-gradient(160deg, var(--c-green-light), #fff 60%);
        display: flex; align-items: center; justify-content: center;
        box-shadow: var(--shadow-lg);
        position: relative;
    }
    .hero-visual-ring::before {
        content: ''; position: absolute; inset: -10px; border-radius: 50%;
        border: 2px dashed rgba(11,170,114,0.28);
    }
    .hero-visual-ring img { width: 78%; height: 78%; object-fit: contain; filter: drop-shadow(0 12px 26px rgba(7,91,58,0.25)); }
    .hero-card {
        position: absolute; bottom: -12px; right: -8px;
        background: var(--c-white); border-radius: 18px; padding: 16px 18px;
        box-shadow: var(--shadow-md); width: 200px; border: 1px solid var(--c-border);
    }
    .hero-card-img {
        height: 64px; border-radius: 12px; background: var(--c-green-light);
        display: flex; align-items: center; justify-content: center; font-size: 34px; margin-bottom: 10px;
    }
    .hero-card-title { font-weight: 700; font-size: 13px; color: var(--c-text); }
    .hero-card-price { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
    .hero-card-price strong { color: var(--c-green-dark); font-size: 15px; }
    .rating { font-size: 10px; color: var(--c-text-light); }
    .hero-gold-chip {
        position: absolute; top: 4px; left: -14px;
        background: var(--c-gold); color: var(--c-green-dark);
        font-weight: 800; font-size: 11.5px; padding: 7px 14px; border-radius: 50px;
        box-shadow: 0 6px 14px rgba(242,201,76,0.4);
    }

    /* ---------- SECTIONS ---------- */
    section { padding: 96px 0; }
    .section-header { text-align: center; max-width: 700px; margin: 0 auto 52px; }
    .section-badge {
        display: inline-block; background: var(--c-green-light); color: var(--c-green-dark);
        padding: 7px 18px; border-radius: 50px; font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 18px;
    }
    .section-title { font-size: 36px; font-weight: 900; letter-spacing: -0.8px; line-height: 1.18; margin-bottom: 14px; }
    .section-title span { color: var(--c-gold-dark); }
    .section-subtitle { font-size: 16.5px; color: var(--c-text-light); }

    /* ---------- STATS BAND ---------- */
    .stats-band { background: var(--c-green-dark); position: relative; overflow: hidden; padding: 64px 0; }
    .stats-band::before {
        content: ''; position: absolute; top: -60%; right: -10%; width: 420px; height: 420px;
        background: radial-gradient(circle, rgba(255,255,255,0.06), transparent 70%); border-radius: 50%;
    }
    .stats-band-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; z-index: 1; }
    .stat-box { text-align: center; padding: 8px 12px; }
    .stat-box .stat-icon { font-size: 26px; margin-bottom: 10px; }
    .stat-box h3 { color: var(--c-white); font-size: 34px; font-weight: 900; letter-spacing: -0.5px; }
    .stat-box p { color: rgba(255,255,255,0.72); font-size: 13px; font-weight: 500; margin-top: 4px; }

    /* ---------- NOSOTROS / SPLIT ---------- */
    .split { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
    .split-media { position: relative; }
    .split-media img {
        width: 100%; height: 420px; object-fit: cover; border-radius: 28px;
        box-shadow: var(--shadow-lg);
    }
    .split-media-badge {
        position: absolute; bottom: -22px; left: -22px;
        background: var(--c-white); border-radius: 18px; padding: 16px 20px;
        box-shadow: var(--shadow-md); border: 1px solid var(--c-border);
        display: flex; align-items: center; gap: 12px;
    }
    .split-media-badge .smb-icon {
        width: 44px; height: 44px; border-radius: 12px; background: var(--c-green-light);
        display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
    }
    .split-media-badge strong { display: block; font-size: 17px; color: var(--c-green-dark); }
    .split-media-badge span { font-size: 11.5px; color: var(--c-text-light); }
    .split-media-dot { position: absolute; top: -18px; right: -18px; width: 70px; height: 70px; border-radius: 50%; background: var(--c-gold); opacity: 0.85; z-index: -1; }
    .split-text .section-badge { margin-bottom: 16px; }
    .split-text h2 { font-size: 32px; font-weight: 900; letter-spacing: -0.6px; line-height: 1.2; margin-bottom: 16px; }
    .split-text p { color: var(--c-text-light); font-size: 15.5px; margin-bottom: 22px; }
    .split-points { display: flex; flex-direction: column; gap: 14px; margin-bottom: 26px; }
    .split-point { display: flex; align-items: flex-start; gap: 12px; }
    .split-point-icon {
        width: 30px; height: 30px; border-radius: 9px; background: var(--c-green);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 14px; flex-shrink: 0; margin-top: 2px;
    }
    .split-point strong { font-size: 14.5px; color: var(--c-text); display: block; }
    .split-point span { font-size: 13.5px; color: var(--c-text-light); }

    /* ---------- BENEFICIOS ---------- */
    .benefits { background: var(--c-bg-soft); }
    .benefits-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .benefit-card {
        background: var(--c-white); border-radius: var(--radius); padding: 34px 26px;
        box-shadow: var(--shadow-sm); transition: all .3s ease; border: 1px solid var(--c-border);
    }
    .benefit-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); border-color: transparent; }
    .benefit-icon {
        width: 56px; height: 56px; border-radius: 15px; margin-bottom: 18px;
        display: flex; align-items: center; justify-content: center; font-size: 26px;
        background: var(--c-green-light);
    }
    .benefit-icon.amber, .benefit-icon.gold { background: #FBF0D3; }
    .benefit-icon.blue { background: #E3F1FB; }
    .benefit-icon.purple { background: #ECE7FA; }
    .benefit-card h3 { font-size: 18.5px; font-weight: 800; margin-bottom: 10px; color: var(--c-text); }
    .benefit-card p { color: var(--c-text-light); font-size: 14px; }

    /* ---------- PRODUCTOS ---------- */
    .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
    .product-card {
        display: block; background: var(--c-white); border-radius: 22px; overflow: hidden;
        box-shadow: var(--shadow-sm); transition: all .3s ease; border: 1px solid var(--c-border);
    }
    .product-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); border-color: transparent; }
    .product-img {
        height: 210px; background-color: var(--c-green-light);
        background-size: cover; background-position: center;
        display: flex; align-items: center; justify-content: center;
        font-size: 84px; position: relative;
    }
    .product-body { padding: 22px 24px 24px; }
    .product-cat {
        font-size: 11px; font-weight: 700; color: var(--c-green-dark);
        text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;
    }
    .product-card h3 { font-size: 19px; font-weight: 800; margin-bottom: 8px; color: var(--c-text); }
    .product-card p { color: var(--c-text-light); font-size: 13.5px; margin-bottom: 16px; min-height: 42px; }
    .product-footer {
        display: flex; justify-content: space-between; align-items: center;
        padding-top: 16px; border-top: 1px solid var(--c-border);
    }
    .product-price { font-size: 22px; font-weight: 900; color: var(--c-green-dark); }
    .product-price-row { display: flex; align-items: baseline; gap: 5px; margin-bottom: 4px; }
    .product-price-currency { font-size: 13px; font-weight: 700; color: var(--c-green-dark); }
    .product-price-unit { font-size: 12px; font-weight: 600; color: var(--c-text-light); }
    .product-add-form { padding: 0 24px 24px; }
    .product-add-btn { width: 100%; justify-content: center; padding: 11px; font-size: 13.5px; }
    .stock-badge { padding: 4px 11px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
    .stock-badge.ok { background: var(--c-green-light); color: var(--c-green-dark); }
    .stock-badge.low { background: #FBF0D3; color: #8A6A16; }

    /* ---------- TESTIMONIOS ---------- */
    .testimonials { background: var(--c-bg-soft); }
    .testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }
    .testimonial-card {
        background: var(--c-white); border-radius: var(--radius); padding: 30px 26px;
        box-shadow: var(--shadow-sm); border: 1px solid var(--c-border);
    }
    .testimonial-stars { color: var(--c-gold-dark); font-size: 14px; margin-bottom: 12px; }
    .testimonial-text { font-size: 14.5px; color: var(--c-text); margin-bottom: 20px; font-style: italic; }
    .testimonial-author { display: flex; align-items: center; gap: 12px; }
    .testimonial-avatar {
        width: 42px; height: 42px; border-radius: 50%; background: var(--c-green-light);
        color: var(--c-green-dark); display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 14px; flex-shrink: 0;
    }
    .testimonial-name { font-size: 13.5px; font-weight: 700; color: var(--c-text); }
    .testimonial-role { font-size: 12px; color: var(--c-text-light); }

    /* ---------- CTA ---------- */
    .cta-section { padding: 40px 0 96px; }
    .cta-box {
        background: linear-gradient(120deg, var(--c-green-dark) 0%, #0a7a4f 100%);
        border-radius: 30px; padding: 60px; position: relative; overflow: hidden;
        display: grid; grid-template-columns: 1.3fr 1fr; gap: 40px; align-items: center;
    }
    .cta-box::before {
        content: ''; position: absolute; top: -110px; right: -90px; width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(242,201,76,0.22), transparent 70%); border-radius: 50%;
    }
    .cta-box::after {
        content: ''; position: absolute; bottom: -120px; left: -60px; width: 260px; height: 260px;
        background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 70%); border-radius: 50%;
    }
    .cta-content { position: relative; z-index: 2; }
    .cta-content h2 { font-size: 32px; font-weight: 900; color: var(--c-white); letter-spacing: -0.6px; line-height: 1.2; margin-bottom: 14px; }
    .cta-content p { font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px; max-width: 420px; }
    .cta-buttons { display: flex; gap: 14px; flex-wrap: wrap; }

    /* ---------- FOOTER ---------- */
    footer { background: var(--c-green-dark); color: rgba(255,255,255,0.72); padding: 72px 0 28px; position: relative; }
    .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 50px; }
    .footer-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .footer-logo .logo-icon { border: 2px solid rgba(255,255,255,0.25); }
    .footer-logo .lw-main { color: var(--c-white); }
    .footer-logo .lw-sub { color: var(--c-gold); }
    .footer-desc { font-size: 14px; line-height: 1.7; margin-bottom: 22px; max-width: 320px; }
    .footer-socials { display: flex; gap: 10px; }
    .social-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(255,255,255,0.08);
        display: flex; align-items: center; justify-content: center;
        transition: all .2s ease;
    }
    .social-icon:hover { background: var(--c-gold); color: var(--c-green-dark); }
    .footer-col h4 { color: var(--c-white); font-weight: 700; font-size: 14.5px; margin-bottom: 18px; letter-spacing: 0.3px; }
    .footer-col ul { list-style: none; }
    .footer-col ul li { margin-bottom: 11px; }
    .footer-col ul li a { font-size: 14px; transition: color .2s; }
    .footer-col ul li a:hover { color: var(--c-gold); }
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.14);
        padding-top: 24px;
        display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        font-size: 13px;
    }
    .footer-bottom a:hover { color: var(--c-gold); }

    /* ---------- FORMS (contacto) ---------- */
    .form-card {
        background: var(--c-white); border-radius: 26px; padding: 42px;
        box-shadow: var(--shadow-md); border: 1px solid var(--c-border);
    }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--c-text); margin-bottom: 7px; }
    .form-input, .form-textarea {
        width: 100%; padding: 12px 14px; border-radius: 12px;
        border: 1.5px solid var(--c-border); font-size: 14px; background: var(--c-bg-soft);
        transition: all .2s ease; font-family: inherit; color: var(--c-text);
    }
    .form-input:focus, .form-textarea:focus {
        outline: none; border-color: var(--c-green); background: var(--c-white);
        box-shadow: 0 0 0 4px rgba(11,170,114,0.12);
    }
    .form-textarea { resize: vertical; min-height: 120px; }
    .alert { padding: 14px 18px; border-radius: 14px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
    .alert-success { background: var(--c-green-light); color: var(--c-green-dark); border: 1px solid rgba(7,91,58,0.15); }
    .alert-error { background: #FCEAEA; color: #9B2C2C; border: 1px solid #F5C6C6; }

    /* ---------- PAGINATION ---------- */
    .pagination { display: flex; gap: 6px; justify-content: center; margin-top: 40px; flex-wrap: wrap; }
    .pagination a, .pagination span {
        padding: 8px 14px; border-radius: 10px; background: var(--c-white);
        border: 1px solid var(--c-border); font-size: 13px; font-weight: 600; color: var(--c-text);
        transition: all .2s ease;
    }
    .pagination a:hover { border-color: var(--c-green); color: var(--c-green-dark); }
    .pagination span.active { background: var(--c-green-dark); color: var(--c-white); border-color: var(--c-green-dark); }

    /* ---------- PRODUCT DETAIL ---------- */
    .product-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start; padding: 156px 0 80px; }
    .product-detail-img {
        height: 400px; border-radius: 28px; background-color: var(--c-green-light);
        background-size: cover; background-position: center;
        display: flex; align-items: center; justify-content: center; font-size: 140px;
        box-shadow: var(--shadow-md);
    }
    .product-detail-spec { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--c-border); font-size: 14px; }
    .product-detail-spec strong { color: var(--c-green-dark); }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 1024px) {
        .hero-inner, .split { grid-template-columns: 1fr; }
        .hero-visual { margin-top: 30px; order: -1; }
        .split-media { order: -1; }
        .benefits-grid, .stats-band-grid { grid-template-columns: repeat(2, 1fr); }
        .products-grid, .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
        .cta-box { grid-template-columns: 1fr; text-align: center; }
        .cta-content p { margin-left: auto; margin-right: auto; }
        .cta-buttons { justify-content: center; }
        .footer-grid { grid-template-columns: 1fr 1fr; }
        .hero h1 { font-size: 36px; }
        .section-title { font-size: 27px; }
        .nav-links { display: none; }
        .hero { padding: 132px 0 50px; }
        section { padding: 64px 0; }
        .product-detail { grid-template-columns: 1fr; padding-top: 132px; }
    }
    @media (max-width: 640px) {
        .container { padding: 0 18px; }
        .hero h1 { font-size: 30px; letter-spacing: -0.8px; }
        .hero p { font-size: 15.5px; }
        .hero-cta { flex-direction: column; align-items: stretch; }
        .hero-cta .btn { justify-content: center; }
        .benefits-grid, .products-grid, .testimonials-grid, .stats-band-grid { grid-template-columns: 1fr; }
        .footer-grid { grid-template-columns: 1fr; gap: 32px; }
        .footer-bottom { flex-direction: column; text-align: center; }
        .cta-box { padding: 40px 24px; }
        .split-media img { height: 280px; }
        .split-media-badge { left: 10px; bottom: -18px; padding: 12px 16px; }
        .nav-cta .btn-outline { display: none; }
        .logo-wordmark .lw-main { font-size: 15.5px; }
        .form-card { padding: 26px; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var items = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window) || items.length === 0) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        items.forEach(function (el) { observer.observe(el); });
    });
</script>
