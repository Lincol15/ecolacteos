<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 50px;">
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">🛒 Mi Carrito</div>
            <h1>Tu <span>Carrito de Compras</span></h1>
        </div>
    </div>
</section>

<section style="padding-top:0;">
    <div class="container" style="max-width:800px;">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        @forelse($items as $item)
        <div class="benefit-card reveal is-visible" style="margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:220px;">
                <div style="width:52px;height:52px;border-radius:12px;background:var(--c-green-light);background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;font-size:26px;{{ $item['product']->image_url ? "background-image:url('{$item['product']->image_url}')" : '' }}">
                    @unless($item['product']->image_url) {{ $item['product']->emoji ?? '🧀' }} @endunless
                </div>
                <div>
                    <strong>{{ $item['product']->name }}</strong>
                    <div style="font-size:13px;color:var(--c-text-light);">S/ {{ number_format($item['product']->unit_price, 2) }} / {{ $item['product']->unit }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('cart.update', $item['product']) }}" style="display:flex;align-items:center;gap:8px;">
                @csrf
                @method('PUT')
                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-input" style="width:70px;">
                <button type="submit" class="btn btn-outline" style="padding:8px 14px;">Actualizar</button>
            </form>
            <div style="font-weight:800;color:var(--c-green-dark);min-width:90px;text-align:right;">S/ {{ number_format($item['subtotal'], 2) }}</div>
            <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline" style="padding:8px 10px;">🗑️</button>
            </form>
        </div>
        @empty
        <div style="text-align:center;padding:60px 20px;color:var(--c-text-light)">
            <div style="font-size:50px;margin-bottom:14px">🛒</div>
            <h3>Tu carrito está vacío</h3>
            <p style="margin-top:8px;"><a href="{{ route('catalog') }}" class="link" style="color:var(--c-green-dark);font-weight:700;">Explora el catálogo</a> y agrega productos.</p>
        </div>
        @endforelse

        @if($items->isNotEmpty())
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:20px;border-top:1px solid var(--c-border);">
            <div style="font-size:20px;font-weight:900;color:var(--c-green-dark);">Total: S/ {{ number_format($total, 2) }}</div>
            <a href="{{ route('cart.checkout') }}" class="btn btn-primary">Finalizar Pedido →</a>
        </div>
        @endif
    </div>
</section>

@include('public.partials.footer')
</body>
</html>
