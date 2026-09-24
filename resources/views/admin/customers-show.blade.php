@extends('layouts.app')

@section('page-title', $customer->name)
@section('page-subtitle', 'Cliente de la tienda web')

@section('top-actions')
    <a href="{{ route('admin.customers') }}" class="btn btn-ghost">← Clientes</a>
@endsection

@section('content')
<div class="grid-2" style="align-items:start">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👤 Datos del cliente</div>
        </div>
        <div class="panel-body">
            <div class="pay-breakdown">
                <div class="pay-line"><span>Nombre</span><strong>{{ $customer->name }}</strong></div>
                <div class="pay-line"><span>Correo</span><strong><a href="mailto:{{ $customer->email }}" class="link">{{ $customer->email }}</a></strong></div>
                <div class="pay-line"><span>Teléfono</span><strong>{{ $customer->phone ?: '—' }}</strong></div>
                <div class="pay-line"><span>Dirección</span><strong style="text-align:right">{{ $customer->address ?: '—' }}</strong></div>
                <div class="pay-line"><span>Cuenta creada</span><strong>{{ $customer->created_at?->format('d/m/Y H:i') }}</strong></div>
            </div>
        </div>
    </div>

    <div class="pay-kpis" style="grid-template-columns:1fr; margin:0;">
        <div class="pay-kpi">
            <div class="pay-kpi-icon blue"><x-icon name="bag" /></div>
            <div><div class="pay-kpi-label">Pedidos realizados</div><div class="pay-kpi-value">{{ $summary['orders'] }}</div></div>
        </div>
        <div class="pay-kpi">
            <div class="pay-kpi-icon green"><x-icon name="dollar" /></div>
            <div><div class="pay-kpi-label">Total comprado</div><div class="pay-kpi-value">S/ {{ number_format($summary['total'], 2) }}</div></div>
        </div>
        <div class="pay-kpi">
            <div class="pay-kpi-icon amber"><x-icon name="clock" /></div>
            <div><div class="pay-kpi-label">Pedidos por cobrar</div><div class="pay-kpi-value">{{ $summary['pending'] }}</div></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧾 Historial de pedidos</div>
    </div>
    <div class="table-wrap">
        <table class="pay-table">
            <thead><tr><th>Pedido</th><th>Fecha</th><th>Productos</th><th>Total</th><th>Pago</th><th style="text-align:right"></th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><code class="badge badge-gray">{{ $order->invoice_number ?? '#'.$order->id }}</code></td>
                    <td>{{ $order->sale_date?->format('d/m/Y') }}</td>
                    <td class="pay-muted" style="max-width:320px">{{ $order->items->map(fn ($i) => ($i->product?->name ?? 'Producto').' × '.rtrim(rtrim(number_format($i->quantity, 2, '.', ''), '0'), '.'))->implode(', ') ?: '—' }}</td>
                    <td class="pay-amount">S/ {{ number_format($order->total_amount, 2) }}</td>
                    <td><span class="badge {{ $order->payment_status === 'pagado' ? 'badge-green' : ($order->payment_status === 'anulado' ? 'badge-gray' : 'badge-amber') }}">{{ \App\Models\Sale::PAYMENT_STATUS[$order->payment_status] ?? ucfirst($order->payment_status) }}</span></td>
                    <td><div class="pay-actions"><a href="{{ route('admin.sales-show', $order) }}" class="btn-icon"><x-icon name="eye" /> Ver pedido</a></div></td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><div class="empty-icon">🛒</div><h3>Este cliente aún no hizo pedidos</h3></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div style="padding:16px 20px">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
