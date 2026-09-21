@extends('layouts.app')

@section('page-title', 'Gestión de Inventario')
@section('page-subtitle', 'Stock de productos y movimientos de inventario')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Productos en Stock</div>
        <div class="stat-value green">{{ $products->total() }}</div>
        <div class="stat-icon-wrap green">📦</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Sin Stock</div>
        <div class="stat-value amber">{{ collect($stock)->filter(fn ($qty) => $qty <= 0)->count() }}</div>
        <div class="stat-icon-wrap amber">⚠️</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Entradas Recientes</div>
        <div class="stat-value blue">{{ $movements->where('movement_type', 'entrada')->count() }}</div>
        <div class="stat-icon-wrap blue">⬇️</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Salidas Recientes</div>
        <div class="stat-value red">{{ $movements->whereIn('movement_type', ['salida', 'merma'])->count() }}</div>
        <div class="stat-icon-wrap red">⬆️</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Stock Actual por Producto</div>
    </div>
    <div class="panel-body">
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            @forelse($products as $product)
            @php($qty = $stock[$product->id] ?? 0)
            <div style="padding:16px; background:#f8fafc; border-radius:14px; border:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <div>
                        <strong style="font-size:14px;">{{ $product->name }}</strong>
                        <div style="font-size:11px; color:var(--text-light);">SKU: {{ $product->sku ?? '—' }}</div>
                    </div>
                    <span class="badge badge-blue">{{ $product->unit }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:22px; font-weight:800; color:var(--text);">{{ number_format($qty, 2) }}</div>
                    @if($qty <= 0)
                    <span class="badge badge-red">⚠️ Sin stock</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1;">
                <div class="empty">
                    <div class="empty-icon">📦</div>
                    <h3>No hay productos activos</h3>
                    <p>Los movimientos de entrada generarán el stock automáticamente.</p>
                </div>
            </div>
            @endforelse
        </div>
        {{ $products->links() }}
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Movimientos Recientes de Inventario</div>
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
                    @forelse($movements as $mov)
                    <tr>
                        <td><strong>{{ $mov->created_at?->format('d/m/Y H:i') }}</strong></td>
                        <td>
                            <strong>{{ $mov->product->name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">Ref: {{ $mov->reference_document ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ ['entrada' => 'green', 'salida' => 'red', 'ajuste' => 'blue', 'merma' => 'red', 'devolucion' => 'amber'][$mov->movement_type] ?? 'gray' }}">
                                {{ \App\Models\Inventory::MOVEMENT_TYPES[$mov->movement_type] ?? $mov->movement_type }}
                            </span>
                        </td>
                        <td><strong>{{ in_array($mov->movement_type, ['salida', 'merma']) ? '-' : '+' }}{{ number_format($mov->quantity, 2) }}</strong></td>
                        <td>S/{{ number_format($mov->unit_cost ?? 0, 2) }}</td>
                        <td>{{ $mov->location ?? '—' }}</td>
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
