@extends('layouts.app')

@section('title', 'Detalle Lote - VACA SYS')
@section('page-title', 'Lote de Producción')
@section('page-subtitle', 'Información detallada y gestión del lote')

@section('top-actions')
    <a href="{{ route('plant.batches') }}" class="btn btn-ghost">← Volver a Lotes</a>
@endsection

@section('content')
@if(isset($batch))
<div class="stats-grid">
    <div class="stat-card purple">
        <div class="stat-icon-wrap purple">📦</div>
        <div class="stat-label">Código Lote</div>
        <div class="stat-value purple" style="font-size:22px">{{ $batch->batch_code ?? 'LOT-' . $batch->id }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">🏷️</div>
        <div class="stat-label">Producto</div>
        <div class="stat-value green" style="font-size:18px">{{ $batch->product?->name ?? 'N/A' }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📊</div>
        <div class="stat-label">Cantidad Producida</div>
        <div class="stat-value blue">{{ number_format($batch->quantity ?? 0, 2) }} {{ $batch->unit ?? 'u' }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">
            @if($batch->status === 'planeado') 📋
            @elseif($batch->status === 'en_proceso') ⚙️
            @elseif($batch->status === 'curando') ⏳
            @elseif($batch->status === 'terminado') ✅
            @elseif($batch->status === 'vendido') 💰
            @else 🗑️
            @endif
        </div>
        <div class="stat-label">Estado Actual</div>
        <div>
            <span class="badge {{ $batch->status === 'planeado' ? 'badge-gray' : ($batch->status === 'en_proceso' ? 'badge-blue' : ($batch->status === 'curando' ? 'badge-amber' : ($batch->status === 'terminado' ? 'badge-green' : ($batch->status === 'vendido' ? 'badge-purple' : 'badge-red')))) }}" style="font-size:14px;padding:6px 14px">
                {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
            </span>
        </div>
    </div>
</div>

<div class="panel" style="margin-bottom:24px">
    <div class="panel-header">
        <div class="panel-title">🔄 Actualizar Estado del Lote</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('plant.batch-update-status', $batch->id) }}">
            @csrf
            @method('PUT')
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px">
                <button type="submit" name="status" value="planeado" class="btn btn-ghost {{ $batch->status === 'planeado' ? 'ring-2 ring-gray-400' : '' }}">📋 Planeado</button>
                <button type="submit" name="status" value="en_proceso" class="btn btn-info {{ $batch->status === 'en_proceso' ? '' : '' }}">⚙️ En Proceso</button>
                <button type="submit" name="status" value="curando" class="btn btn-accent">⏳ Curando</button>
                <button type="submit" name="status" value="terminado" class="btn btn-primary">✅ Terminado</button>
                <button type="submit" name="status" value="vendido" class="btn btn-purple">💰 Vendido</button>
                <button type="submit" name="status" value="desperdicio" class="btn btn-danger">🗑️ Desperdicio</button>
            </div>
        </form>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📋 Información General</div>
        </div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Fecha Producción</div>
                    <div style="font-weight:700">{{ $batch->production_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Fecha Vencimiento</div>
                    <div style="font-weight:700">{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Leche Utilizada</div>
                    <div style="font-weight:800;color:#059669">{{ number_format($batch->milk_used ?? 0, 2) }} L</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Costo Unitario</div>
                    <div style="font-weight:700">S/ {{ number_format($batch->unit_cost ?? 0, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Costo Total</div>
                    <div style="font-weight:800;color:#d97706">S/ {{ number_format(($batch->quantity ?? 0) * ($batch->unit_cost ?? 0), 2) }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Supervisor</div>
                    <div style="font-weight:700">{{ $batch->supervisor?->fullname ?? '-' }}</div>
                </div>
            </div>
            @if(!empty($batch->notes))
            <div style="margin-top:16px">
                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;margin-bottom:6px">Notas de Producción</div>
                <div style="background:#f8fafc;padding:12px 16px;border-radius:10px;border-left:4px solid #8b5cf6;font-size:14px">
                    {{ $batch->notes }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚛 Entregas de Leche Utilizadas</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Entrega #</th><th>Productor</th><th>Litros</th>
                </tr></thead>
                <tbody>
                    @forelse($batch->milkDeliveries ?? [] as $d)
                    <tr>
                        <td style="font-weight:600;color:#2563eb">#{{ $d->id }}</td>
                        <td>{{ $d->producer?->user?->fullname ?? '-' }}</td>
                        <td style="font-weight:700">{{ number_format($d->liters, 2) }} L</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="empty" style="padding:30px 20px"><div class="empty-icon" style="font-size:40px">🚛</div><h3 style="font-size:15px">Sin entregas asociadas</h3></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Movimientos de Inventario Asociados</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Costo Unit.</th><th>Ubicación</th><th>Notas</th>
            </tr></thead>
            <tbody>
                @forelse($batch->inventoryMovements ?? [] as $m)
                <tr>
                    <td style="font-size:13px">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $m->movement_type === 'entrada' ? 'badge-green' : ($m->movement_type === 'salida' ? 'badge-red' : ($m->movement_type === 'ajuste' ? 'badge-blue' : 'badge-amber')) }}">
                            {{ ucfirst($m->movement_type) }}
                        </span>
                    </td>
                    <td style="font-weight:700">{{ number_format($m->quantity ?? 0, 2) }}</td>
                    <td>S/ {{ number_format($m->unit_cost ?? 0, 2) }}</td>
                    <td>{{ $m->location ?? '-' }}</td>
                    <td style="font-size:13px;color:#64748b">{{ \Illuminate\Support\Str::limit($m->notes ?? '-', 30) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty"><div class="empty-icon">📦</div><h3>Sin movimientos de inventario</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-body">
        <div class="empty">
            <div class="empty-icon">📦</div>
            <h3>Lote no encontrado</h3>
            <p>El lote de producción solicitado no existe.</p>
            <a href="{{ route('plant.batches') }}" class="btn btn-primary" style="margin-top:16px">← Volver a Lotes</a>
        </div>
    </div>
</div>
@endif
@endsection
