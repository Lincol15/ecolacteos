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
        <div>
            <div class="panel-title">📖 Recetas de Producción</div>
            <div class="form-hint" style="margin-top:4px">Qué ingredientes lleva cada producto. Las cantidades totales se calculan solas en Producción.</div>
        </div>
        <a href="{{ route('plant.recipe-create') }}" class="btn btn-sm btn-primary">➕ Nueva Receta</a>
    </div>
    <div class="panel-body">
        @if($recipes->isEmpty())
        <div class="empty"><div class="empty-icon">📖</div><h3>Sin recetas registradas</h3><p>Crea la primera receta para poder producir.</p></div>
        @else
        <div class="recipe-grid">
            @foreach($recipes as $r)
            <article class="recipe-card">
                <header class="recipe-head">
                    <div>
                        <div class="recipe-product">{{ $r->product?->name ?? 'Producto eliminado' }}</div>
                        <div class="recipe-name">{{ $r->name }} · por 1 {{ $r->product?->unit ?? 'und' }}</div>
                    </div>
                    <span class="badge {{ $r->active ? 'badge-green' : 'badge-gray' }}">{{ $r->active ? 'Activa' : 'Inactiva' }}</span>
                </header>

                <ul class="recipe-ingredients">
                    @forelse($r->recipeIngredients as $ri)
                    <li>
                        <span>{{ $ri->ingredient?->is_milk ? '🥛' : '🧂' }} {{ $ri->ingredient?->name ?? 'Insumo eliminado' }}</span>
                        <strong>{{ rtrim(rtrim(number_format($ri->quantity_per_unit, 3, '.', ''), '0'), '.') }} {{ $ri->ingredient?->unit }}</strong>
                    </li>
                    @empty
                    <li class="recipe-empty">Sin ingredientes registrados.</li>
                    @endforelse
                </ul>

                @if($r->instructions)
                <details class="recipe-instructions">
                    <summary>Ver instrucciones</summary>
                    <p>{{ $r->instructions }}</p>
                </details>
                @endif

                <footer class="recipe-foot">
                    <form method="POST" action="{{ route('plant.recipe-destroy', $r) }}" onsubmit="return confirm('¿Eliminar la receta &quot;{{ $r->name }}&quot;? Los lotes ya producidos no se modifican.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-ghost" style="color:#B42318">🗑️ Eliminar receta</button>
                    </form>
                </footer>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
    .recipe-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
    .recipe-card { border: 1px solid var(--border); border-radius: 16px; padding: 18px; background: #fff; display: flex; flex-direction: column; gap: 12px; }
    .recipe-head { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
    .recipe-product { font-family: 'Poppins', 'Inter', sans-serif; font-size: 16px; font-weight: 700; }
    .recipe-name { font-size: 12.5px; color: var(--text-light); }
    .recipe-ingredients { list-style: none; display: grid; gap: 6px; }
    .recipe-ingredients li { display: flex; justify-content: space-between; gap: 10px; padding: 8px 12px; border-radius: 10px; background: #F6FBF8; font-size: 14px; }
    .recipe-ingredients .recipe-empty { color: var(--text-light); background: none; padding: 0; }
    .recipe-instructions summary { cursor: pointer; font-size: 13px; font-weight: 600; color: var(--primary); }
    .recipe-instructions p { font-size: 13px; color: var(--text-light); margin-top: 6px; white-space: pre-line; }
    .recipe-foot { display: flex; justify-content: flex-end; margin-top: auto; padding-top: 6px; border-top: 1px dashed var(--border); }
</style>
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
</div>

<x-milk-received-by-day :report="$milkReport" :action="route('plant.production')" :hidden="['tab' => 'insumos']" />

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
