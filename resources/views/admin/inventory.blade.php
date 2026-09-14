@extends('layouts.app')

@section('page-title', 'Gestión de Inventario')
@section('page-subtitle', 'Stock de productos y movimientos de inventario')

@section('top-actions')
    <a href="{{ route('admin.inventory') }}" class="btn btn-accent">
        📥 Reporte Stock
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Productos en Stock</div>
        <div class="stat-value green">{{ ($inventory ?? collect())->count() }}</div>
        <div class="stat-icon-wrap green">📦</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Stock Bajo</div>
        <div class="stat-value amber">{{ ($inventory ?? collect())->filter(fn($i) => ($i->quantity ?? 0) < ($i->min_stock ?? 10))->count() }}</div>
        <div class="stat-icon-wrap amber">⚠️</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Entradas (Mes)</div>
        <div class="stat-value blue">{{ ($movements ?? collect())->where('type', 'entrada')->count() }}</div>
        <div class="stat-icon-wrap blue">⬇️</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Salidas (Mes)</div>
        <div class="stat-value red">{{ ($movements ?? collect())->where('type', 'salida')->count() }}</div>
        <div class="stat-icon-wrap red">⬆️</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Stock Actual por Producto</div>
    </div>
    <div class="panel-body">
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            @forelse($inventory ?? collect() as $item)
            <div style="padding:16px; background:#f8fafc; border-radius:14px; border:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <div>
                        <strong style="font-size:14px;">{{ $item->product->name ?? 'Producto #'.$item->product_id }}</strong>
                        <div style="font-size:11px; color:var(--text-light);">SKU: {{ $item->product->sku ?? '—' }}</div>
                    </div>
                    <span class="badge badge-blue">{{ $item->product->unit ?? 'un' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <div style="font-size:22px; font-weight:800; color:var(--text);">{{ number_format($item->quantity, 0) }}</div>
                    @php
                        $qty = $item->quantity ?? 0;
                        $max = $item->max_stock ?? ($qty * 2);
                        $pct = $max > 0 ? min(100, ($qty / $max) * 100) : 0;
                        $barColor = $qty < ($item->min_stock ?? 10) ? 'red' : ($pct > 75 ? 'green' : 'amber');
                    @endphp
                    <div style="font-size:11px; color:var(--text-light);">Min: {{ $item->min_stock ?? 0 }} / Max: {{ $max }}</div>
                </div>
                <div class="progress-wrap">
                    <div class="progress {{ $barColor }}" style="width:{{ $pct }}%"></div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:12px;">
                    <span style="color:var(--text-light);">Ubicación: {{ $item->location ?? 'Almacén 1' }}</span>
                    <span style="color:var(--text-light);">Costo: S/{{ number_format($item->unit_cost ?? 0, 2) }}</span>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1;">
                <div class="empty">
                    <div class="empty-icon">📦</div>
                    <h3>No hay stock registrado</h3>
                    <p>Los movimientos de entrada generarán el stock automáticamente.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Movimientos de Inventario</div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Costo Unit.</th>
                        <th>Ubicación</th>
                        <th>Procesado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? collect() as $mov)
                    <tr>
                        <td>
                            <strong>{{ $mov->movement_date ? \Carbon\Carbon::parse($mov->movement_date)->format('d/m/Y H:i') : '—' }}</strong>
                        </td>
                        <td>
                            <strong>{{ $mov->product->name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">Lote: {{ $mov->batch_code ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $mov->type == 'entrada' ? 'green' : ($mov->type == 'salida' ? 'red' : ($mov->type == 'ajuste' ? 'blue' : 'amber')) }}">
                                {{ $mov->type == 'entrada' ? '⬇️ Entrada' : ($mov->type == 'salida' ? '⬆️ Salida' : ($mov->type == 'ajuste' ? '🔄 Ajuste' : '📦 Transferencia')) }}
                            </span>
                        </td>
                        <td><strong>{{ $mov->type == 'salida' ? '-' : '+' }}{{ number_format($mov->quantity, 0) }}</strong></td>
                        <td>S/{{ number_format($mov->unit_cost ?? 0, 2) }}</td>
                        <td>{{ $mov->location ?? 'Almacén 1' }}</td>
                        <td>{{ $mov->processedBy->name ?? 'Sistema' }} {{ $mov->processedBy->lastname ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <div class="empty-icon">📋</div>
                                <h3>No hay movimientos registrados</h3>
                                <p>Los movimientos se generan automáticamente con compras, producción y ventas.</p>
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
