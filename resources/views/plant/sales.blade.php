@extends('layouts.app')

@section('page-title', 'Ventas / Salidas')
@section('page-subtitle', 'Stock disponible, nuevas ventas e historial')

@section('content')
<div class="filters" style="margin-bottom:20px;">
    <a href="{{ route('plant.sales', ['tab' => 'stock']) }}" class="btn btn-sm {{ $tab === 'stock' ? 'btn-primary' : 'btn-ghost' }}">📦 Stock Disponible</a>
    <a href="{{ route('plant.sales-create') }}" class="btn btn-sm btn-accent">➕ Nueva Venta</a>
    <a href="{{ route('plant.sales', ['tab' => 'historial']) }}" class="btn btn-sm {{ $tab === 'historial' ? 'btn-primary' : 'btn-ghost' }}">📋 Historial de Ventas</a>
</div>

@if($tab === 'stock')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Stock Disponible por Producto</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Producto</th><th>Categoría</th><th>Stock</th></tr></thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->category ?? '-' }}</td>
                    <td style="font-weight:800;color:{{ ($stocks[$p->id] ?? 0) > 30 ? '#059669' : (($stocks[$p->id] ?? 0) > 10 ? '#d97706' : '#dc2626') }}">
                        {{ number_format($stocks[$p->id] ?? 0, 2) }} {{ $p->unit }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="empty"><div class="empty-icon">📦</div><h3>Sin productos disponibles</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if($tab === 'historial')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧾 Historial de Ventas</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('plant.sales', ['tab' => 'historial']) }}" class="filters">
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('plant.sales', ['tab' => 'historial']) }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>N° Factura</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Estado Pago</th></tr></thead>
                <tbody>
                    @forelse($sales as $s)
                    <tr>
                        <td style="font-weight:700;color:#2563eb">{{ $s->invoice_number }}</td>
                        <td>{{ $s->sale_date?->format('d/m/Y') }}</td>
                        <td>{{ $s->client_name }}</td>
                        <td style="font-weight:700">S/ {{ number_format($s->total_amount, 2) }}</td>
                        <td>
                            <span class="badge {{ $s->payment_status === 'pagado' ? 'badge-green' : ($s->payment_status === 'anulado' ? 'badge-red' : 'badge-amber') }}">{{ ucfirst($s->payment_status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">🧾</div><h3>Sin ventas registradas</h3></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($sales, 'links'))
        <div style="padding:20px">{{ $sales->links() }}</div>
        @endif
    </div>
</div>
@endif
@endsection
