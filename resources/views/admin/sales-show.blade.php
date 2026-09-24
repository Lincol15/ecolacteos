@extends('layouts.app')

@section('page-title', 'Venta ' . $sale->invoice_number)
@section('page-subtitle', match($sale->sale_type) {
    'pedido_web' => '🌐 Pedido realizado desde la tienda pública',
    default => 'Detalle de la venta'
})

@section('top-actions')
    <a href="{{ route('admin.sales') }}" class="btn btn-ghost">← Volver a Ventas</a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total del Pedido</div>
        <div class="stat-value green">S/ {{ number_format($sale->total_amount, 2) }}</div>
        <div class="stat-icon-wrap green">💰</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Subtotal</div>
        <div class="stat-value blue">S/ {{ number_format($sale->subtotal, 2) }}</div>
        <div class="stat-icon-wrap blue">🧾</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Impuesto</div>
        <div class="stat-value amber">S/ {{ number_format($sale->tax, 2) }}</div>
        <div class="stat-icon-wrap amber">📊</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Origen</div>
        <div class="stat-value purple" style="font-size:16px;">
            {{ \App\Models\Sale::SALE_TYPES[$sale->sale_type] ?? $sale->sale_type }}
        </div>
        <div class="stat-icon-wrap purple">📍</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">👤 Datos del Cliente</div>
    </div>
    <div class="panel-body">
        <div class="form-grid">
            <div><span class="form-label">Nombre</span><div><strong>{{ $sale->client_name }}</strong></div></div>
            <div><span class="form-label">Email</span><div>{{ $sale->client_email ?? '—' }}</div></div>
            <div><span class="form-label">Teléfono</span><div>{{ $sale->client_phone ?? '—' }}</div></div>
            <div><span class="form-label">DNI/RUC</span><div>{{ $sale->client_dni_ruc ?? '—' }}</div></div>
            <div style="grid-column: span 2;"><span class="form-label">Dirección de Entrega</span><div>{{ $sale->client_address ?? '—' }}</div></div>
            @if($sale->customer)
            <div style="grid-column: span 2;">
                <span class="badge badge-purple">👤 Cliente registrado desde {{ $sale->customer->created_at->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($sale->notes)
            <div style="grid-column: span 2;"><span class="form-label">Notas</span><div>{{ $sale->notes }}</div></div>
            @endif
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧀 Productos del Pedido</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td><strong>{{ $item->product->name ?? 'Producto eliminado' }}</strong></td>
                    <td>{{ number_format($item->quantity, 2) }} {{ $item->product->unit ?? '' }}</td>
                    <td>S/ {{ number_format($item->unit_price, 2) }}</td>
                    <td><strong>S/ {{ number_format($item->subtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Estado del Pedido</div>
    </div>
    <div class="panel-body">
        <div style="margin-bottom:16px;">
            <span class="badge {{ match($sale->payment_status) {
                'pagado' => 'badge-green',
                'pendiente' => 'badge-red',
                'parcial' => 'badge-amber',
                default => 'badge-gray'
            } }}" style="font-size:14px; padding:8px 16px;">
                {{ \App\Models\Sale::PAYMENT_STATUS[$sale->payment_status] ?? $sale->payment_status }}
            </span>
        </div>

        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.sales-update-status', $sale) }}" style="display:flex; gap:10px; align-items:end;">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Cambiar estado</label>
                <select name="payment_status" class="form-select">
                    @foreach(\App\Models\Sale::PAYMENT_STATUS as $value => $label)
                    <option value="{{ $value }}" {{ $sale->payment_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">💾 Actualizar</button>
        </form>
        @endif
    </div>
</div>
@endsection
