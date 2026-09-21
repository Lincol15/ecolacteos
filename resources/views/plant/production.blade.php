@extends('layouts.app')

@section('page-title', 'Producción')
@section('page-subtitle', 'Recetas, nuevos lotes e historial de producción')

@section('content')
<div class="filters" style="margin-bottom:20px;">
    <a href="{{ route('plant.production', ['tab' => 'recetas']) }}" class="btn btn-sm {{ $tab === 'recetas' ? 'btn-primary' : 'btn-ghost' }}">📖 Recetas</a>
    <a href="{{ route('plant.production', ['tab' => 'insumos']) }}" class="btn btn-sm {{ $tab === 'insumos' ? 'btn-primary' : 'btn-ghost' }}">🧂 Insumos</a>
    <a href="{{ route('plant.production-create') }}" class="btn btn-sm btn-accent">➕ Nueva Producción</a>
    <a href="{{ route('plant.production', ['tab' => 'historial']) }}" class="btn btn-sm {{ $tab === 'historial' ? 'btn-primary' : 'btn-ghost' }}">📋 Historial de Lotes</a>
</div>

@if($tab === 'recetas')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📖 Recetas de Producción</div>
        <a href="{{ route('plant.recipe-create') }}" class="btn btn-sm btn-primary">➕ Nueva Receta</a>
    </div>
    <div class="panel-body" style="padding-top:8px;">
        @forelse($recipes as $r)
        <div style="padding:16px 0; border-bottom:1px solid #f1f5f9;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <strong style="font-size:15px;">{{ $r->name }}</strong>
                    <div style="font-size:12px; color:var(--text-light);">Producto terminado: {{ $r->product?->name ?? 'N/A' }}</div>
                </div>
                <span class="badge {{ $r->active ? 'badge-green' : 'badge-gray' }}">{{ $r->active ? 'Activa' : 'Inactiva' }}</span>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @forelse($r->recipeIngredients as $ri)
                <span class="badge {{ $ri->ingredient?->is_milk ? 'badge-blue' : 'badge-gray' }}">
                    {{ $ri->ingredient?->is_milk ? '🥛' : '🧂' }} {{ $ri->ingredient?->name ?? 'N/A' }}: {{ number_format($ri->quantity_per_unit, 2) }} {{ $ri->ingredient?->unit }}
                </span>
                @empty
                <span style="font-size:12px; color:var(--text-light);">Sin ingredientes registrados.</span>
                @endforelse
            </div>
        </div>
        @empty
        <div class="empty"><div class="empty-icon">📖</div><h3>Sin recetas registradas</h3><p>Crea la primera receta para agilizar la carga de producción.</p></div>
        @endforelse
    </div>
</div>
@endif

@if($tab === 'insumos')
<div class="stats-grid">
    @foreach($ingredients as $ing)
    <div class="stat-card {{ $ing->is_milk ? 'blue' : 'green' }}">
        <div class="stat-icon-wrap {{ $ing->is_milk ? 'blue' : 'green' }}">{{ $ing->is_milk ? '🥛' : '🧂' }}</div>
        <div class="stat-label">{{ $ing->name }}</div>
        <div class="stat-value {{ $ing->is_milk ? 'blue' : 'green' }}">{{ number_format($ing->currentStock(), 2) }} {{ $ing->unit }}</div>
        <x-ingredient-stock-note :ingredient="$ing" />
    </div>
    @endforeach
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">🚛</div>
        <div class="stat-label">Leche Recibida Hoy</div>
        <div class="stat-value" style="color:#0891b2">{{ number_format($milkReceivedToday, 2) }} L</div>
        <div style="font-size:11px; color:var(--text-light); margin-top:4px;">Entregas ya analizadas por Calidad hoy</div>
    </div>
</div>

<div class="panel" style="background:#f8fafc;">
    <div class="panel-body" style="font-size:13px; color:var(--text-light);">
        ℹ️ Esta vista es solo de consulta. El registro de nuevos insumos y de compras se gestiona desde el panel de Administración.
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Movimientos Recientes de Insumos</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Insumo</th><th>Tipo</th><th>Cantidad</th><th>Lote</th><th>Notas</th></tr></thead>
            <tbody>
                @forelse($ingredientMovements as $m)
                <tr>
                    <td style="font-size:12px;color:var(--text-light)">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->ingredient?->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ match($m->movement_type) { 'entrada' => 'badge-green', 'salida' => 'badge-red', 'merma' => 'badge-orange', default => 'badge-blue' } }}">
                            {{ \App\Models\IngredientMovement::MOVEMENT_TYPES[$m->movement_type] ?? $m->movement_type }}
                        </span>
                    </td>
                    <td style="font-weight:700">{{ number_format($m->quantity, 2) }} {{ $m->ingredient?->unit }}</td>
                    <td>{{ $m->productionBatch?->batch_number ?? '-' }}</td>
                    <td style="font-size:12px;color:var(--text-light)">{{ $m->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty"><div class="empty-icon">📋</div><h3>Sin movimientos registrados</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if($tab === 'historial')
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card" style="background:rgba(148,163,184,0.1)">
        <div class="stat-label">Total Lotes</div>
        <div class="stat-value" style="color:#64748b">{{ $stats['total_batches'] }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">En Proceso</div>
        <div class="stat-value blue">{{ $stats['in_process'] }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Curando</div>
        <div class="stat-value amber">{{ $stats['curing'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Terminados</div>
        <div class="stat-value green">{{ $stats['finished'] }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Historial de Lotes de Producción</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Lote</th><th>Fecha</th><th>Producto</th><th>Leche Usada</th><th>Rendimiento</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse($batches as $b)
                <tr>
                    <td style="font-weight:700;color:#7c3aed">
                        {{ $b->batch_number }}
                        @if($b->group_number)
                        <div><span class="badge badge-blue" style="font-weight:400;font-size:10px;" title="Creado junto a otros productos en el mismo carrito">🛒 {{ $b->group_number }}</span></div>
                        @endif
                    </td>
                    <td>{{ $b->production_date?->format('d/m/Y') }}</td>
                    <td>{{ $b->product?->name ?? 'N/A' }}</td>
                    <td>{{ number_format($b->input_milk_liters, 2) }} L</td>
                    <td>{{ number_format($b->yield_percentage, 1) }}%</td>
                    <td>
                        <span class="badge {{ match($b->status) { 'terminado' => 'badge-green', 'curando' => 'badge-amber', 'en_proceso' => 'badge-blue', 'vendido' => 'badge-purple', 'desperdicio' => 'badge-red', default => 'badge-gray' } }}">
                            {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                        </span>
                    </td>
                    <td><a href="{{ route('plant.batch-show', $b) }}" class="btn btn-sm btn-ghost">👁️ Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">📦</div><h3>Sin lotes registrados</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($batches, 'links'))
    <div style="padding:20px">{{ $batches->links() }}</div>
    @endif
</div>
@endif
@endsection
