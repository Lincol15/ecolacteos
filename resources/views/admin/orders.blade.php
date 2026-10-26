@extends('layouts.app')

@section('page-title', 'Pedidos')
@section('page-subtitle', 'Pedidos de la tienda web y su estado de entrega')

@php
    $canManage = auth()->user()->isAdmin();
    $nextLabels = ['confirmado' => '✔ Confirmar pedido', 'en_camino' => '🚚 Enviar', 'entregado' => '📦 Marcar entregado'];
    $pill = fn (?string $status) => match ($status) {
        'nuevo' => 'status-pending',
        'confirmado', 'en_camino' => 'status-processing',
        'entregado' => 'status-paid',
        default => 'status-rejected',
    };
@endphp

@section('content')
<div class="pay-kpis">
    <div class="pay-kpi">
        <div class="pay-kpi-icon amber"><x-icon name="bell" /></div>
        <div>
            <div class="pay-kpi-label">Pedidos nuevos</div>
            <div class="pay-kpi-value">{{ $stats['nuevo'] }}</div>
            <div class="pay-kpi-hint">Esperan confirmación</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon blue"><x-icon name="truck" /></div>
        <div>
            <div class="pay-kpi-label">En preparación o camino</div>
            <div class="pay-kpi-value">{{ $stats['en_proceso'] }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon green"><x-icon name="check" /></div>
        <div>
            <div class="pay-kpi-label">Entregados este mes</div>
            <div class="pay-kpi-value">{{ $stats['entregado_mes'] }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon purple"><x-icon name="dollar" /></div>
        <div>
            <div class="pay-kpi-label">Por cobrar</div>
            <div class="pay-kpi-value">S/ {{ number_format($stats['por_cobrar'], 2) }}</div>
            <div class="pay-kpi-hint">Pedidos activos sin pagar</div>
        </div>
    </div>
</div>

<nav class="pay-tabs" aria-label="Estado del pedido">
    <a href="{{ route('admin.orders', ['search' => request('search')]) }}" class="pay-tab {{ $status === null ? 'active' : '' }}">Todos <small>· {{ $counts->sum() }}</small></a>
    @foreach(\App\Models\Sale::ORDER_STATUS as $key => $label)
    <a href="{{ route('admin.orders', ['status' => $key, 'search' => request('search')]) }}" class="pay-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} <small>· {{ $counts[$key] ?? 0 }}</small></a>
    @endforeach
</nav>

<div class="panel">
    <form method="GET" action="{{ route('admin.orders') }}" class="pay-toolbar">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <div class="form-group grow">
            <label class="form-label">Buscar pedido</label>
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="N° de pedido, cliente o teléfono...">
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            <a href="{{ route('admin.orders', array_filter(['status' => $status])) }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </div>
    </form>

    <div class="panel-body">
        @if($orders->isEmpty())
        <div class="empty">
            <div class="empty-icon">🛒</div>
            <h3>{{ request('search') || $status ? 'No hay pedidos con estos filtros' : 'Aún no hay pedidos' }}</h3>
            <p>Los pedidos que hagan los clientes en la tienda web aparecerán aquí.</p>
        </div>
        @else
        <div class="order-grid">
            @foreach($orders as $order)
            <article class="order-card">
                <header class="order-head">
                    <div>
                        <strong>{{ $order->invoice_number ?? '#'.$order->id }}</strong>
                        <div class="pay-muted">{{ $order->sale_date?->format('d/m/Y') }} · {{ $order->created_at?->format('H:i') }}</div>
                    </div>
                    <span class="status-pill {{ $pill($order->order_status) }}"><span class="status-dot"></span>{{ \App\Models\Sale::ORDER_STATUS[$order->order_status] ?? 'Sin estado' }}</span>
                </header>

                <div class="order-client">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <strong>{{ $order->client_name }}</strong>
                        @if($order->customer)
                        <a href="{{ route('admin.customers-show', $order->customer) }}" class="badge badge-purple" style="text-decoration:none">Con cuenta</a>
                        @else
                        <span class="badge badge-gray">Invitado</span>
                        @endif
                    </div>
                    @if($order->client_phone)<div class="pay-muted">📞 {{ $order->client_phone }}</div>@endif
                    @if($order->client_address)<div class="pay-muted">📍 {{ $order->client_address }}</div>@endif
                </div>

                <ul class="order-items">
                    @foreach($order->items as $item)
                    <li><span>{{ $item->product?->name ?? 'Producto' }} × {{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</span><span>S/ {{ number_format($item->subtotal, 2) }}</span></li>
                    @endforeach
                </ul>

                <div class="order-total">
                    <span class="badge {{ $order->payment_status === 'pagado' ? 'badge-green' : ($order->payment_status === 'anulado' ? 'badge-gray' : 'badge-amber') }}">
                        Pago: {{ \App\Models\Sale::PAYMENT_STATUS[$order->payment_status] ?? $order->payment_status }}
                    </span>
                    <strong>S/ {{ number_format($order->total_amount, 2) }}</strong>
                </div>

                <footer class="order-actions">
                    @if($canManage && ($next = $order->nextOrderStatus()))
                    <form method="POST" action="{{ route('admin.orders-update-status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="order_status" value="{{ $next }}">
                        <button type="submit" class="btn btn-primary btn-sm">{{ $nextLabels[$next] }}</button>
                    </form>
                    @endif
                    @if($canManage && $order->payment_status !== 'pagado' && $order->order_status !== 'cancelado')
                    <form method="POST" action="{{ route('admin.sales-update-status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="payment_status" value="pagado">
                        <button type="submit" class="btn btn-ghost btn-sm">💵 Marcar pagado</button>
                    </form>
                    @endif
                    <a href="{{ route('admin.sales-show', $order) }}" class="btn btn-ghost btn-sm">Ver detalle</a>
                    @if($canManage && $order->canBeCancelled())
                    <form method="POST" action="{{ route('admin.orders-update-status', $order) }}" onsubmit="return confirm('¿Cancelar el pedido {{ $order->invoice_number }}? Los productos volverán al stock.')" style="margin-left:auto">
                        @csrf @method('PUT')
                        <input type="hidden" name="order_status" value="cancelado">
                        <button type="submit" class="btn btn-ghost btn-sm" style="color:#B42318">Cancelar</button>
                    </form>
                    @endif
                </footer>
            </article>
            @endforeach
        </div>
        @endif
    </div>
    @if($orders->hasPages())
    <div style="padding:0 20px 16px">{{ $orders->links() }}</div>
    @endif
</div>

<style>
    .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
    .order-card { border: 1px solid var(--border); border-radius: 16px; padding: 16px; background: #fff; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04); }
    .order-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .order-head strong { font-family: 'Poppins', 'Inter', sans-serif; font-size: 15px; }
    .order-client { display: grid; gap: 3px; padding: 10px 12px; border-radius: 12px; background: #F6FBF8; font-size: 13.5px; }
    .order-items { list-style: none; display: grid; gap: 4px; font-size: 13px; }
    .order-items li { display: flex; justify-content: space-between; gap: 10px; }
    .order-total { display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px dashed var(--border); }
    .order-total strong { font-family: 'Poppins', 'Inter', sans-serif; font-size: 18px; }
    .order-actions { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: auto; }
</style>
@endsection
