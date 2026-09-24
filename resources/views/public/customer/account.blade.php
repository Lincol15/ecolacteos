<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 50px;">
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">👤 Mi Cuenta</div>
            <h1>Hola, <span>{{ $customer->name }}</span></h1>
            <p style="margin:0 auto;">{{ $customer->email }}</p>
        </div>
    </div>
</section>

<section style="padding-top:0;">
    <div class="container" style="max-width:900px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h2 style="font-size:22px;">Historial de Pedidos</h2>
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Cerrar Sesión</button>
            </form>
        </div>

        @forelse($sales as $sale)
        <div class="benefit-card reveal is-visible" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <strong>{{ $sale->invoice_number }}</strong>
                <div style="font-size:13px;color:var(--c-text-light);">{{ $sale->sale_date->format('d/m/Y') }} · {{ $sale->items->count() }} producto(s)</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:18px;font-weight:800;color:var(--c-green-dark);">S/ {{ number_format($sale->total_amount, 2) }}</div>
                <span class="stock-badge {{ $sale->payment_status === 'pagado' ? 'ok' : 'low' }}">{{ \App\Models\Sale::PAYMENT_STATUS[$sale->payment_status] ?? $sale->payment_status }}</span>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:60px 20px;color:var(--c-text-light)">
            <div style="font-size:50px;margin-bottom:14px">🧺</div>
            <h3>Aún no tienes pedidos</h3>
            <p><a href="{{ route('catalog') }}" class="link" style="color:var(--c-green-dark);font-weight:700;">Explora el catálogo</a> y arma tu primer pedido.</p>
        </div>
        @endforelse

        @if($sales->hasPages())
        <div class="pagination">{{ $sales->links() }}</div>
        @endif
    </div>
</section>

@include('public.partials.footer')
</body>
</html>
