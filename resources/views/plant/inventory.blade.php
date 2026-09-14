@extends('layouts.app')

@section('title', 'Inventario - VACA SYS')
@section('page-title', 'Gestión de Inventario')
@section('page-subtitle', 'Control de stock y movimientos de productos terminados')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">📦</div>
        <div class="stat-label">Productos en Stock</div>
        <div class="stat-value green">{{ count($stock ?? []) }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📊</div>
        <div class="stat-label">Unidades Totales</div>
        <div class="stat-value blue">{{ collect($stock ?? [])->sum('quantity') }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💲</div>
        <div class="stat-label">Valor Inventario</div>
        <div class="stat-value amber">S/ {{ number_format(collect($stock ?? [])->sum(fn($s) => ($s->quantity ?? 0) * ($s->unit_cost ?? 0)), 2) }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon-wrap red">⚠️</div>
        <div class="stat-label">Stock Bajo</div>
        <div class="stat-value red">{{ count(collect($stock ?? [])->filter(fn($s) => ($s->quantity ?? 0) < 10)) }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📦 Stock Actual por Producto</div>
        </div>
        <div class="panel-body" style="padding-bottom:8px">
            @forelse($stock ?? [] as $s)
            <div style="padding:12px 0;border-bottom:1px solid #f1f5f9">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ecfeff,#cffafe);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                            {{ $s->product?->icon ?? '🧀' }}
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14.5px">{{ $s->product?->name ?? 'Producto' }}</div>
                            <div style="font-size:12px;color:#94a3b8">{{ $s->location ?? 'Almacén' }} · {{ $s->product?->category ?? 'Sin categoría' }}</div>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-weight:900;color:{{ ($s->quantity ?? 0) > 30 ? '#059669' : (($s->quantity ?? 0) > 10 ? '#d97706' : '#dc2626') }};font-size:20px">
                            {{ number_format($s->quantity ?? 0, 2) }}
                        </div>
                        <div style="font-size:11px;color:#94a3b8">{{ $s->unit ?? 'u' }} · S/ {{ number_format($s->unit_cost ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="progress-wrap" style="height:6px">
                    <div class="progress {{ ($s->quantity ?? 0) > 30 ? '' : (($s->quantity ?? 0) > 10 ? 'amber' : 'red') }}" style="width:{{ min((($s->quantity ?? 0) / 100) * 100, 100) }}%"></div>
                </div>
            </div>
            @empty
            <div class="empty"><div class="empty-icon">📦</div><h3>Sin stock registrado</h3><p>No hay productos en inventario.</p></div>
            @endforelse
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">➕ Registrar Movimiento de Inventario</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('plant.inventory-adjust') }}">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Producto *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Seleccionar producto --</option>
                            @forelse($products ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo Movimiento *</label>
                        <select name="movement_type" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="entrada">📥 Entrada</option>
                            <option value="salida">📤 Salida</option>
                            <option value="ajuste">⚙️ Ajuste</option>
                            <option value="merma">⚠️ Merma</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" class="form-input" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Costo Unitario (S/)</label>
                        <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost') }}" class="form-input" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="location" value="{{ old('location', 'Almacén Principal') }}" class="form-input" placeholder="Almacén Principal">
                    </div>
                    <div class="form-group" style="grid-column:1 / -1">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-textarea" rows="2" placeholder="Detalles del movimiento...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-ghost">Limpiar</button>
                    <button type="submit" class="btn btn-primary">💾 Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Historial de Movimientos</div>
        <div style="display:flex;gap:8px">
            <span class="badge badge-green">📥 Entradas: {{ ($movements ?? collect())->where('movement_type','entrada')->count() }}</span>
            <span class="badge badge-red">📤 Salidas: {{ ($movements ?? collect())->where('movement_type','salida')->count() }}</span>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Costo Unit.</th>
                <th>Ubicación</th>
                <th>Notas</th>
            </tr></thead>
            <tbody>
                @forelse($movements ?? [] as $m)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $m->created_at?->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $m->created_at?->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:18px">{{ $m->product?->icon ?? '📦' }}</span>
                            <span style="font-weight:600">{{ $m->product?->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $m->movement_type === 'entrada' ? 'badge-green' : ($m->movement_type === 'salida' ? 'badge-red' : ($m->movement_type === 'ajuste' ? 'badge-blue' : 'badge-amber')) }}">
                            @if($m->movement_type === 'entrada') 📥
                            @elseif($m->movement_type === 'salida') 📤
                            @elseif($m->movement_type === 'ajuste') ⚙️
                            @else ⚠️ @endif
                            {{ ucfirst($m->movement_type) }}
                        </span>
                    </td>
                    <td style="font-weight:800;color:{{ $m->movement_type === 'entrada' ? '#059669' : '#dc2626' }}">
                        {{ $m->movement_type === 'entrada' ? '+' : '-' }}{{ number_format($m->quantity ?? 0, 2) }}
                    </td>
                    <td>S/ {{ number_format($m->unit_cost ?? 0, 2) }}</td>
                    <td>{{ $m->location ?? '-' }}</td>
                    <td style="font-size:13px;color:#64748b">{{ \Illuminate\Support\Str::limit($m->notes ?? '-', 40) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">📋</div><h3>Sin movimientos registrados</h3><p>Los movimientos de inventario aparecerán aquí.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($movements ?? [], 'links'))
    <div style="padding:20px">
        {{ $movements->links() }}
    </div>
    @endif
</div>
@endsection
