@extends('layouts.app')

@section('title', 'Dashboard Planta - Ecolácteos Huata')
@section('page-title', 'Panel de Planta')
@section('page-subtitle', 'Control de producción, lotes e inventario en tiempo real')

@section('top-actions')
    <a href="{{ route('plant.batches') }}" class="btn btn-accent">
        <span>📦</span> Ver Lotes
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">🥛</div>
        <div class="stat-label">Leche Recibida Hoy</div>
        <div class="stat-value green">{{ number_format($todayMilk ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon-wrap purple">🏭</div>
        <div class="stat-label">Lotes Activos</div>
        <div class="stat-value purple">{{ count($activeBatches ?? []) }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">📦</div>
        <div class="stat-label">Stock Productos</div>
        <div class="stat-value amber">
            {{ collect($pendingStock ?? [])->sum('quantity') }}
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">💰</div>
        <div class="stat-label">Valor Inventario</div>
        <div class="stat-value blue">
            S/ {{ number_format(collect($pendingStock ?? [])->sum(fn($s) => ($s->quantity ?? 0) * ($s->unit_cost ?? 0)), 2) }}
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📈 Lotes Producidos (Última Semana)</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <canvas id="chartBatches"></canvas>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🏭 Lotes en Proceso</div>
            <a href="{{ route('plant.batches') }}" class="btn btn-sm btn-ghost">Ver todos →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Código</th><th>Producto</th><th>Cantidad</th><th>Estado</th>
                </tr></thead>
                <tbody>
                    @forelse($activeBatches ?? [] as $b)
                    <tr>
                        <td style="font-weight:700;color:#7c3aed">{{ $b->batch_code ?? 'LOT-' . $b->id }}</td>
                        <td>
                            <div style="font-weight:600">{{ $b->product?->name ?? 'N/A' }}</div>
                        </td>
                        <td style="font-weight:700">{{ number_format($b->quantity ?? 0, 2) }} {{ $b->unit ?? 'u' }}</td>
                        <td>
                            @if($b->status === 'planeado') <span class="badge badge-gray">📋 Planeado</span>
                            @elseif($b->status === 'en_proceso') <span class="badge badge-blue">⚙️ En Proceso</span>
                            @elseif($b->status === 'curando') <span class="badge badge-amber">⏳ Curando</span>
                            @elseif($b->status === 'terminado') <span class="badge badge-green">✅ Terminado</span>
                            @elseif($b->status === 'vendido') <span class="badge badge-purple">💰 Vendido</span>
                            @else <span class="badge badge-red">🗑️ Desperdicio</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty"><div class="empty-icon">🏭</div><h3>Sin lotes activos</h3><p>No hay lotes en producción actualmente.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📦 Stock por Producto</div>
            <a href="{{ route('plant.inventory') }}" class="btn btn-sm btn-ghost">Gestionar →</a>
        </div>
        <div class="panel-body" style="padding-bottom:8px">
            @forelse($pendingStock ?? [] as $s)
            <div style="padding:12px 0;border-bottom:1px solid #f1f5f9">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#d1fae5,#a7f3d0);display:flex;align-items:center;justify-content:center;font-size:18px">
                            {{ $s->product?->icon ?? '🧀' }}
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px">{{ $s->product?->name ?? 'Producto' }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $s->location ?? 'Almacén Principal' }}</div>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:800;color:#059669;font-size:18px">{{ number_format($s->quantity ?? 0, 2) }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $s->unit ?? 'u' }} · S/ {{ number_format($s->unit_cost ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="progress-wrap" style="height:6px">
                    <div class="progress {{ ($s->quantity ?? 0) > 50 ? '' : (($s->quantity ?? 0) > 20 ? 'amber' : 'red') }}" style="width:{{ min(($s->quantity ?? 0), 100) }}%"></div>
                </div>
            </div>
            @empty
            <div class="empty"><div class="empty-icon">📦</div><h3>Sin stock registrado</h3></div>
            @endforelse
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:24px">
            <div class="panel-header">
                <div class="panel-title">📢 Notificaciones</div>
            </div>
            <div class="panel-body" style="padding:12px 0">
                @forelse($notifications ?? [] as $n)
                <div style="padding:14px 22px;border-left:4px solid {{ $n->priority === 'urgente' ? '#ef4444' : ($n->priority === 'alta' ? '#f59e0b' : '#3b82f6') }};margin-bottom:4px;background:{{ $n->priority === 'urgente' ? '#fef2f2' : ($n->priority === 'alta' ? '#fffbeb' : '#eff6ff') }}">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
                        <div style="font-weight:700;font-size:14px">{{ $n->title }}</div>
                        <span class="badge {{ $n->priority === 'urgente' ? 'badge-red' : ($n->priority === 'alta' ? 'badge-amber' : 'badge-blue') }}">{{ $n->priority }}</span>
                    </div>
                    <div style="font-size:13px;color:#475569">{{ $n->message }}</div>
                </div>
                @empty
                <div class="empty" style="padding:30px 20px"><div class="empty-icon" style="font-size:40px">📢</div><h3 style="font-size:15px">Sin notificaciones</h3></div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📋 Lotes Recientes</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Código</th><th>Producto</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse($recentBatches ?? [] as $b)
                        <tr>
                            <td style="font-weight:600;color:#7c3aed">{{ $b->batch_code ?? 'LOT-' . $b->id }}</td>
                            <td>{{ $b->product?->name ?? '-' }}</td>
                            <td style="font-size:12px;color:#64748b">{{ $b->production_date?->format('d/m') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty" style="padding:30px 20px"><h3 style="font-size:15px">Sin lotes recientes</h3></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartBatches'), {
        type: 'bar',
        data: {
            labels: @json($labels ?? []),
            datasets: [{
                label: 'Lotes producidos',
                data: @json($batchCount ?? []),
                backgroundColor: 'rgba(139, 92, 246, 0.85)',
                borderRadius: 10,
                borderSkipped: false
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
});
</script>
@endsection
