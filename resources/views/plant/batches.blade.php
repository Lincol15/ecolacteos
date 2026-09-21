@extends('layouts.app')

@section('title', 'Lotes de Producción - Ecolácteos Huata')
@section('page-title', 'Gestión de Lotes')
@section('page-subtitle', 'Control y seguimiento de lotes de producción láctea')

@section('top-actions')
    <a href="{{ route('admin.production-create') }}" class="btn btn-primary">
        <span>➕</span> Nuevo Lote
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🔍 Filtros</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('plant.batches') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="planeado" {{ request('status') === 'planeado' ? 'selected' : '' }}>📋 Planeado</option>
                    <option value="en_proceso" {{ request('status') === 'en_proceso' ? 'selected' : '' }}>⚙️ En Proceso</option>
                    <option value="curando" {{ request('status') === 'curando' ? 'selected' : '' }}>⏳ Curando</option>
                    <option value="terminado" {{ request('status') === 'terminado' ? 'selected' : '' }}>✅ Terminado</option>
                    <option value="vendido" {{ request('status') === 'vendido' ? 'selected' : '' }}>💰 Vendido</option>
                    <option value="desperdicio" {{ request('status') === 'desperdicio' ? 'selected' : '' }}>🗑️ Desperdicio</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Producto</label>
                <select name="product_id" class="form-select">
                    <option value="">Todos</option>
                    @forelse($products ?? [] as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-end">
                <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                <a href="{{ route('plant.batches') }}" class="btn btn-ghost">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(6,1fr)">
    <div class="stat-card" style="background:rgba(148,163,184,0.1)">
        <div class="stat-label">Planeados</div>
        <div class="stat-value" style="color:#64748b">{{ ($batches ?? collect())->where('status','planeado')->count() }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">En Proceso</div>
        <div class="stat-value blue">{{ ($batches ?? collect())->where('status','en_proceso')->count() }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Curando</div>
        <div class="stat-value amber">{{ ($batches ?? collect())->where('status','curando')->count() }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Terminados</div>
        <div class="stat-value green">{{ ($batches ?? collect())->where('status','terminado')->count() }}</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Vendidos</div>
        <div class="stat-value purple">{{ ($batches ?? collect())->where('status','vendido')->count() }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Desperdicio</div>
        <div class="stat-value red">{{ ($batches ?? collect())->where('status','desperdicio')->count() }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Lista de Lotes de Producción</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Leche Usada</th>
                <th>Estado</th>
                <th>Supervisor</th>
                <th>Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($batches ?? [] as $b)
                <tr>
                    <td style="font-weight:700;color:#7c3aed">{{ $b->batch_code ?? 'LOT-' . $b->id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $b->production_date?->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $b->expiry_date ? 'Vence: ' . $b->expiry_date->format('d/m/Y') : '' }}</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:20px">{{ $b->product?->icon ?? '🧀' }}</span>
                            <div>
                                <div style="font-weight:600">{{ $b->product?->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:700">{{ number_format($b->quantity ?? 0, 2) }} {{ $b->unit ?? 'u' }}</td>
                    <td>{{ number_format($b->milk_used ?? 0, 2) }} L</td>
                    <td>
                        @if($b->status === 'planeado') <span class="badge badge-gray">📋 Planeado</span>
                        @elseif($b->status === 'en_proceso') <span class="badge badge-blue">⚙️ En Proceso</span>
                        @elseif($b->status === 'curando') <span class="badge badge-amber">⏳ Curando</span>
                        @elseif($b->status === 'terminado') <span class="badge badge-green">✅ Terminado</span>
                        @elseif($b->status === 'vendido') <span class="badge badge-purple">💰 Vendido</span>
                        @else <span class="badge badge-red">🗑️ Desperdicio</span> @endif
                    </td>
                    <td>{{ $b->supervisor?->fullname ?? '-' }}</td>
                    <td>
                        <a href="{{ route('plant.batch-show', $b->id) }}" class="btn btn-sm btn-ghost">👁️ Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="empty"><div class="empty-icon">📦</div><h3>Sin lotes de producción</h3><p>No se encontraron lotes con los filtros seleccionados.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($batches ?? [], 'links'))
    <div style="padding:20px">
        {{ $batches->links() }}
    </div>
    @endif
</div>
@endsection
