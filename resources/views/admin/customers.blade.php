@extends('layouts.app')

@section('page-title', 'Clientes')
@section('page-subtitle', 'Clientes de la tienda web: cuentas registradas y pedidos como invitado')

@section('content')
<div class="pay-kpis">
    <div class="pay-kpi">
        <div class="pay-kpi-icon green"><x-icon name="users" /></div>
        <div>
            <div class="pay-kpi-label">Clientes con cuenta</div>
            <div class="pay-kpi-value">{{ number_format($stats['accounts']) }}</div>
            <div class="pay-kpi-hint">{{ $stats['new_this_month'] }} nuevo(s) este mes</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon amber"><x-icon name="bag" /></div>
        <div>
            <div class="pay-kpi-label">Compradores invitados</div>
            <div class="pay-kpi-value">{{ number_format($stats['guests']) }}</div>
            <div class="pay-kpi-hint">Pidieron sin crear cuenta</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon blue"><x-icon name="dollar" /></div>
        <div>
            <div class="pay-kpi-label">Ventas web totales</div>
            <div class="pay-kpi-value">S/ {{ number_format($stats['web_sales'], 2) }}</div>
            <div class="pay-kpi-hint">Pedidos hechos desde la tienda</div>
        </div>
    </div>
</div>

<nav class="pay-tabs" aria-label="Tipo de cliente">
    <a href="{{ route('admin.customers', ['tab' => 'cuentas', 'search' => request('search')]) }}" class="pay-tab {{ $tab === 'cuentas' ? 'active' : '' }}">
        <x-icon name="users" /> Con cuenta <small>· {{ $stats['accounts'] }}</small>
    </a>
    <a href="{{ route('admin.customers', ['tab' => 'invitados', 'search' => request('search')]) }}" class="pay-tab {{ $tab === 'invitados' ? 'active' : '' }}">
        <x-icon name="bag" /> Invitados <small>· {{ $stats['guests'] }}</small>
    </a>
</nav>

<div class="panel">
    <form method="GET" action="{{ route('admin.customers') }}" class="pay-toolbar">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="form-group grow">
            <label class="form-label">Buscar cliente</label>
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nombre, correo o teléfono...">
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            <a href="{{ route('admin.customers', ['tab' => $tab]) }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </div>
    </form>

    @if($tab === 'cuentas')
    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr><th>Cliente</th><th>Teléfono</th><th>Registrado</th><th>Pedidos</th><th>Total comprado</th><th>Último pedido</th><th style="text-align:right"></th></tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td>
                        <div class="pay-person">
                            <span class="pay-avatar">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $c->name }}</strong>
                                <small>{{ $c->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $c->phone ?: '—' }}</td>
                    <td>
                        <div style="font-weight:600">{{ $c->created_at?->format('d/m/Y') }}</div>
                        <div class="pay-muted">{{ $c->created_at?->diffForHumans() }}</div>
                    </td>
                    <td><span class="badge {{ $c->sales_count ? 'badge-blue' : 'badge-gray' }}">{{ $c->sales_count }}</span></td>
                    <td class="pay-amount">S/ {{ number_format($c->sales_sum_total_amount ?? 0, 2) }}</td>
                    <td>{{ $c->sales_max_sale_date ? \Carbon\Carbon::parse($c->sales_max_sale_date)->format('d/m/Y') : 'Sin pedidos' }}</td>
                    <td><div class="pay-actions"><a href="{{ route('admin.customers-show', $c) }}" class="btn-icon"><x-icon name="eye" /> Ver</a></div></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <div class="empty-icon">👤</div>
                            <h3>{{ request('search') ? 'Sin resultados' : 'Aún no hay clientes registrados' }}</h3>
                            <p>Los clientes aparecen aquí cuando crean su cuenta en la tienda web.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div style="padding:16px 20px">{{ $customers->links() }}</div>
    @endif
    @else
    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr><th>Cliente</th><th>Teléfono</th><th>Pedidos</th><th>Total comprado</th><th>Último pedido</th></tr>
            </thead>
            <tbody>
                @forelse($guests as $g)
                <tr>
                    <td>
                        <div class="pay-person">
                            <span class="pay-avatar" style="background:linear-gradient(135deg,#F2C94C,#D6A927);color:#043521">{{ mb_strtoupper(mb_substr($g->name ?? '?', 0, 1)) }}</span>
                            <div>
                                <strong>{{ $g->name ?: 'Sin nombre' }}</strong>
                                <small>{{ $g->email ?: 'Sin correo' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $g->phone ?: '—' }}</td>
                    <td><span class="badge badge-blue">{{ $g->orders_count }}</span></td>
                    <td class="pay-amount">S/ {{ number_format($g->total_spent, 2) }}</td>
                    <td>{{ $g->last_order ? \Carbon\Carbon::parse($g->last_order)->format('d/m/Y') : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <div class="empty-icon">🛒</div>
                            <h3>{{ request('search') ? 'Sin resultados' : 'Sin pedidos de invitados' }}</h3>
                            <p>Aquí aparecen quienes compran en la tienda web sin crear una cuenta.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($guests->hasPages())
    <div style="padding:16px 20px">{{ $guests->links() }}</div>
    @endif
    @endif
</div>
@endsection
