@extends('layouts.app')

@section('title', 'Dashboard Control Calidad - VACA SYS')
@section('page-title', 'Panel de Laboratorio')
@section('page-subtitle', 'Gestión y control de calidad de leche en tiempo real')

@section('top-actions')
    <a href="{{ route('quality.report-create') }}" class="btn btn-primary">
        <span>➕</span> Nuevo Análisis
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">⏳</div>
        <div class="stat-label">Pendientes de Análisis</div>
        <div class="stat-value amber">{{ $pending ?? 0 }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">✅</div>
        <div class="stat-label">Tasa de Aprobación</div>
        <div class="stat-value green">{{ $approvedRate ?? 0 }}%</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">🧪</div>
        <div class="stat-label">Analizados Hoy</div>
        <div class="stat-value blue">{{ $todayAnalyzed ?? 0 }}</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">📋</div>
        <div class="stat-label">Reportes Totales</div>
        <div class="stat-value" style="color:#0891b2">{{ count($recentReports ?? []) + 0 }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📈 Aprobados vs Rechazados (14 días)</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <canvas id="chartQuality"></canvas>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📊 Parámetros Promedio</div>
        </div>
        <div class="panel-body">
            @forelse($paramStats ?? [] as $param)
            <div style="padding:10px 0; border-bottom:1px solid #f1f5f9">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <div style="font-weight:600;font-size:13.5px">{{ $param['name'] }}</div>
                    <div style="font-weight:700;color:#059669">{{ $param['value'] }}{{ $param['unit'] ?? '' }}</div>
                </div>
                <div class="progress-wrap">
                    <div class="progress" style="width:{{ min($param['percent'] ?? 70, 100) }}%"></div>
                </div>
            </div>
            @empty
            <div class="empty"><div class="empty-icon">📊</div><h3>Sin datos de parámetros</h3></div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">⏳ Entregas Pendientes</div>
            <a href="{{ route('quality.report-create') }}" class="btn btn-sm btn-primary">Analizar →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Entrega</th><th>Productor</th><th>Litros</th><th>Fecha</th><th>Acción</th>
                </tr></thead>
                <tbody>
                    @forelse($pendingDeliveries ?? [] as $d)
                    <tr>
                        <td style="font-weight:600;color:#2563eb">#{{ $d->id }}</td>
                        <td>
                            <div style="font-weight:600">{{ $d->producer?->user?->fullname ?? 'N/A' }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $d->producer?->code ?? '' }}</div>
                        </td>
                        <td style="font-weight:700">{{ number_format($d->liters, 2) }} L</td>
                        <td style="font-size:12px;color:#64748b">{{ $d->delivery_date?->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('quality.report-create') }}?delivery_id={{ $d->id }}" class="btn btn-sm btn-accent">Analizar</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">✅</div><h3>¡Todas analizadas!</h3><p>No hay entregas pendientes.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📋 Reportes Recientes</div>
            <a href="{{ route('quality.reports') }}" class="btn btn-sm btn-ghost">Ver todos →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Código</th><th>Resultado</th><th>Calidad</th><th>Fecha</th><th>Ver</th>
                </tr></thead>
                <tbody>
                    @forelse($recentReports ?? [] as $r)
                    <tr>
                        <td style="font-weight:600">{{ $r->sample_code ?? 'QR-' . $r->id }}</td>
                        <td>
                            @if($r->result === 'aprobado') <span class="badge badge-green">✅ Aprobado</span>
                            @elseif($r->result === 'rechazado') <span class="badge badge-red">❌ Rechazado</span>
                            @elseif($r->result === 'aceptable') <span class="badge badge-blue">👍 Aceptable</span>
                            @else <span class="badge badge-amber">👁️ Observado</span> @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <span class="badge {{ ($r->qualityScore() ?? 0) >= 80 ? 'badge-green' : (($r->qualityScore() ?? 0) >= 60 ? 'badge-amber' : 'badge-red') }}">
                                    {{ $r->qualityScore() ?? 0 }}%
                                </span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#64748b">{{ $r->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('quality.report-show', $r->id) }}" class="btn btn-sm btn-ghost">👁️ Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">📋</div><h3>Sin reportes aún</h3><p>Los análisis aparecerán aquí.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(!empty($notifications ?? []))
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📢 Notificaciones</div>
    </div>
    <div class="panel-body" style="padding:12px 0">
        @forelse($notifications as $n)
        <div style="padding:14px 22px;border-left:4px solid {{ $n->priority === 'urgente' ? '#ef4444' : ($n->priority === 'alta' ? '#f59e0b' : '#3b82f6') }};margin-bottom:4px;background:{{ $n->priority === 'urgente' ? '#fef2f2' : ($n->priority === 'alta' ? '#fffbeb' : '#eff6ff') }}">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
                <div style="font-weight:700;font-size:14px">{{ $n->title }}</div>
                <span class="badge {{ $n->priority === 'urgente' ? 'badge-red' : ($n->priority === 'alta' ? 'badge-amber' : 'badge-blue') }}">{{ $n->priority }}</span>
            </div>
            <div style="font-size:13px;color:#475569">{{ $n->message }}</div>
        </div>
        @empty
        @endforelse
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartQuality'), {
        type: 'bar',
        data: {
            labels: @json($weekLabels ?? []),
            datasets: [
                {
                    label: 'Aprobados',
                    data: @json($weekApproved ?? []),
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                    stack: 'Stack 0'
                },
                {
                    label: 'Rechazados',
                    data: @json($weekRejected ?? []),
                    backgroundColor: 'rgba(239, 68, 68, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                    stack: 'Stack 0'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endsection
