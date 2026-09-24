<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<div class="container">
    <div class="product-detail reveal is-visible">
        <div class="product-detail-img" style="background-image:url('{{ $product->image_url ?? '' }}')">
            @unless($product->image_url) {{ $product->emoji ?? '🧀' }} @endunless
        </div>
        <div>
            <div class="product-cat">{{ \App\Models\Product::CATEGORIES[$product->category] ?? $product->category }}</div>
            <h1 style="font-size:34px;font-weight:900;letter-spacing:-1px;margin:8px 0 14px;">{{ $product->name }}</h1>
            <p style="color:var(--c-text-light);font-size:15.5px;margin-bottom:24px;">{{ $product->long_description ?? $product->description }}</p>

            <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
                <span class="product-price" style="font-size:32px;">S/ {{ number_format($product->unit_price, 2) }}</span>
                <span style="color:var(--c-text-light);font-size:14px;">por {{ $product->unit }}</span>
                <span class="stock-badge {{ $product->currentStock() > 10 ? 'ok' : 'low' }}">{{ number_format($product->currentStock(), 0) }} disponibles</span>
            </div>

            @if($product->specifications)
            <div style="margin-bottom:28px;">
                @foreach($product->specifications as $key => $value)
                <div class="product-detail-spec">
                    <span style="color:var(--c-text-light)">{{ $key }}</span>
                    <strong>{{ $value }}</strong>
                </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('cart.add', $product) }}" class="cta-buttons">
                @csrf
                <input type="number" name="quantity" value="1" min="1" class="form-input" style="width:80px;">
                <button type="submit" class="btn btn-primary">🛒 Agregar al Carrito</button>
                <a href="{{ route('catalog') }}" class="btn btn-outline">← Volver al Catálogo</a>
            </form>
        </div>
    </div>

    @if($related->isNotEmpty())
    <section>
        <div class="section-header reveal">
            <span class="section-badge">También te puede interesar</span>
            <h2 class="section-title">Productos <span>Relacionados</span></h2>
        </div>
        <div class="products-grid">
            @foreach($related as $p)
            <div class="product-card reveal reveal-delay-{{ $loop->iteration % 4 }}">
                <a href="{{ route('product.show', $p->slug) }}">
                    <div class="product-img" style="background-image:url('{{ $p->image_url ?? '' }}')">
                        @unless($p->image_url) {{ $p->emoji ?? '🧀' }} @endunless
                    </div>
                </a>
                <div class="product-body">
                    <div class="product-cat">{{ \App\Models\Product::CATEGORIES[$p->category] ?? $p->category }}</div>
                    <a href="{{ route('product.show', $p->slug) }}" style="color:inherit;">
                        <h3>{{ $p->name }}</h3>
                    </a>
                    <p>{{ $p->description }}</p>
                    <div class="product-footer">
                        <div class="product-price-row">
                            <span class="product-price-currency">S/</span>
                            <span class="product-price">{{ number_format($p->unit_price, 2) }}</span>
                            <span class="product-price-unit">/ {{ $p->unit }}</span>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('cart.add', $p) }}" class="product-add-form">
                    @csrf
                    <button type="submit" class="btn btn-primary product-add-btn">🛒 Agregar</button>
                </form>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

@include('public.partials.cta')
@include('public.partials.footer')
</body>
</html>
