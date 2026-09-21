<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 70px;">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">🧺 Catálogo Completo</div>
            <h1>Nuestros <span>Productos Lácteos</span></h1>
            <p style="margin:0 auto;">Leche fresca, quesos, yogures y mantequilla artesanal, elaborados con leche de productores locales de Huata.</p>
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
        <div class="products-grid">
            @forelse($products as $p)
            <a href="{{ route('product.show', $p->slug) }}" class="product-card reveal reveal-delay-{{ $loop->iteration % 4 }}">
                <div class="product-img" style="background-image:url('{{ $p->image_url ?? '' }}')">
                    @unless($p->image_url) {{ $p->emoji ?? '🧀' }} @endunless
                </div>
                <div class="product-body">
                    <div class="product-cat">{{ \App\Models\Product::CATEGORIES[$p->category] ?? $p->category }}</div>
                    <h3>{{ $p->name }}</h3>
                    <p>{{ $p->description }}</p>
                    <div class="product-footer">
                        <span class="product-price">S/ {{ number_format($p->unit_price, 2) }}</span>
                        <span class="stock-badge {{ $p->currentStock() > 10 ? 'ok' : 'low' }}">{{ number_format($p->currentStock(), 0) }} disp.</span>
                    </div>
                </div>
            </a>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#64748b">
                <div style="font-size:50px;margin-bottom:14px">🧺</div>
                <h3>No hay productos disponibles por el momento</h3>
            </div>
            @endforelse
        </div>

        @if($products->hasPages())
        <div class="pagination">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</section>

@include('public.partials.cta')
@include('public.partials.footer')
</body>
</html>
