<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - Ecolácteos Huata</title>
    @include('public.partials.styles')
</head>
<body>
@include('public.partials.navbar')

<section class="hero" style="padding:150px 0 50px;">
    <div class="container hero-inner" style="grid-template-columns:1fr;text-align:center;">
        <div>
            <div class="hero-badge">✅ Finalizar Pedido</div>
            <h1>Confirma tu <span>Pedido</span></h1>
        </div>
    </div>
</section>

<section style="padding-top:0;">
    <div class="container" style="max-width:1000px;">
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:40px;align-items:start;">
            <div class="form-card reveal is-visible">
                @if($errors->any())
                    <div class="alert alert-error">
                        ❌
                        <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('cart.place-order') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $customer->name ?? '') }}" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="client_email" value="{{ old('client_email', $customer->email ?? '') }}" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="client_phone" value="{{ old('client_phone', $customer->phone ?? '') }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección de Entrega</label>
                        <textarea name="client_address" class="form-textarea">{{ old('client_address', $customer->address ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Método de Pago *</label>
                        <select name="payment_method" required class="form-input">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas del Pedido</label>
                        <textarea name="notes" class="form-textarea" placeholder="Ej: entregar en horario de tarde">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">Confirmar Pedido</button>
                </form>
            </div>

            <div class="benefit-card reveal">
                <h3 style="margin-bottom:16px;">Resumen del Pedido</h3>
                @foreach($items as $item)
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-bottom:1px solid var(--c-border);">
                    <span>{{ $item['quantity'] }}x {{ $item['product']->name }}</span>
                    <strong>S/ {{ number_format($item['subtotal'], 2) }}</strong>
                </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;margin-top:16px;font-size:18px;font-weight:900;color:var(--c-green-dark);">
                    <span>Total</span>
                    <span>S/ {{ number_format($total, 2) }}</span>
                </div>
                <p style="font-size:12.5px;color:var(--c-text-light);margin-top:14px;">* No incluye envío. Nos pondremos en contacto para confirmar la entrega y el pago.</p>
            </div>
        </div>
    </div>
</section>

@include('public.partials.footer')
</body>
</html>
