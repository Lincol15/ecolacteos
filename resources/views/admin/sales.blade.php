@extends('layouts.app')

@section('page-title', 'Gestión de Ventas')
@section('page-subtitle', 'Registro de facturas y órdenes de venta')

@section('top-actions')
    <a href="{{ route('admin.sales-create') }}" class="btn btn-primary">
        ➕ Nueva Venta
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Ventas del Mes</div>
        <div class="stat-value green">{{ $sales->count() }}</div>
        <div class="stat-icon-wrap green">💰</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Total Facturado</div>
        <div class="stat-value blue">S/{{ number_format($sales->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap blue">💵</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes de Pago</div>
        <div class="stat-value amber">S/{{ number_format($sales->where('payment_status', 'pendiente')->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap amber">⏳</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Pagadas</div>
        <div class="stat-value purple">S/{{ number_format($sales->where('payment_status', 'pagado')->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap purple">✅</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧾 Registro de Ventas</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.sales') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="factura" {{ request('type') == 'factura' ? 'selected' : '' }}>Factura</option>
                    <option value="boleta" {{ request('type') == 'boleta' ? 'selected' : '' }}>Boleta</option>
                    <option value="nota_venta" {{ request('type') == 'nota_venta' ? 'selected' : '' }}>Nota Venta</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado Pago</label>
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="pagado" {{ request('payment_status') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="pendiente" {{ request('payment_status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="parcial" {{ request('payment_status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                    <option value="anulado" {{ request('payment_status') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.sales') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>N° Doc.</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <strong>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '—' }}</strong>
                        </td>
                        <td><code class="badge badge-blue">{{ $sale->invoice_number }}</code></td>
                        <td>
                            <strong>{{ $sale->client_name }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $sale->client_email ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($sale->type) {
                                'factura' => 'purple',
                                'boleta' => 'blue',
                                'nota_venta' => 'cyan',
                                default => 'gray'
                            } }}">
                                {{ match($sale->type) {
                                    'factura' => '🧾 Factura',
                                    'boleta' => '📄 Boleta',
                                    'nota_venta' => '📝 Nota Venta',
                                    default => ucfirst($sale->type)
                                } }}
                            </span>
                        </td>
                        <td>S/{{ number_format($sale->subtotal ?? 0, 2) }}</td>
                        <td><strong>S/{{ number_format($sale->total_amount, 2) }}</strong></td>
                        <td>{{ match($sale->payment_method) {
                            'efectivo' => '💵 Efectivo',
                            'transferencia' => '🏦 Transferencia',
                            'tarjeta' => '💳 Tarjeta',
                            'credito' => '📋 Crédito',
                            default => $sale->payment_method ?? '—'
                        } }}</td>
                        <td>
                            <span class="badge badge-{{ match($sale->payment_status) {
                                'pagado' => 'green',
                                'pendiente' => 'red',
                                'parcial' => 'amber',
                                'anulado' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($sale->payment_status) {
                                    'pagado' => '✅ Pagado',
                                    'pendiente' => '❌ Pendiente',
                                    'parcial' => '⏳ Parcial',
                                    'anulado' => '🚫 Anulado',
                                    default => ucfirst($sale->payment_status)
                                } }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm">🔍 Ver</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty">
                                <div class="empty-icon">🧾</div>
                                <h3>No hay ventas registradas</h3>
                                <p>Crea una nueva venta presionando el botón "Nueva Venta".</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
