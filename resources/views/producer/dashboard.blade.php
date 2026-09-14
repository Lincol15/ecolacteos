@extends('layouts.app')

@section('title', 'Dashboard Productor - VACA SYS')
@section('page-title', 'Mi Panel')
@section('page-subtitle', 'Resumen personal de tu producción láctea')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">📅</div>
        <div class="stat-label">Litros del Mes</div>
        <div class="stat-value green">{{ number_format($litersMonth ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📆</div>
        <div class="stat-label">Litros de la Semana</div>
        <div class="stat-value blue">{{ number_format($litersWeek ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💳</div>
        <div class="stat-label">Pagos Pendientes</div>
        <div class="stat-value amber">S/ {{ number_format($pendingPayment ?? 0, 2) }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">✅</div>
        <div class="stat-label">Pagos Realizados</div>
        <div class="stat-value green">S/ {{ number_format($paidPayment ?? 0, 2) }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📊 Producción Últimos 14 Días</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <canvas id="chartProd"></canvas>
            </div>
        </div>
    </div>
    <div>
        <div class="panel" style="margin-bottom:24px">
            <div class="panel-header">
                <div class="panel-title">🚛 Última Entrega</div>
            </div>
            <div class="panel-body">
                @if($lastDelivery)
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Fecha</div>
                        <div style="font-weight:700">{{ $lastDelivery->delivery_date?->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Litros</div>
                        <div style="font-weight:800;color:#059669;font-size:20px">{{ $lastDelivery->liters }} L</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Precio / L</div>
                        <div style="font-weight:700">S/ {{ number_format($lastDelivery->price_per_liter, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Total</div>
                        <div style="font-weight:700;color:#f59e0b">S/ {{ number_format($lastDelivery->total_amount, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Acopiador</div>
                        <div style="font-weight:600">{{ $lastDelivery->collector?->fullname ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Estado</div>
                        <span class="badge {{ $lastDelivery->status === 'rechazado' ? 'badge-red' : ($lastDelivery->status === 'registrado' ? 'badge-amber' : 'badge-green') }}">{{ $lastDelivery->status }}</span>
                    </div>
                </div>
                @else
                <div class="empty"><div class="empty-icon">🚛</div><h3>Aún no tienes entregas</h3><p>Tu primera entrega aparecerá aquí.</p></div>
                @endif
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">🧪 Último Análisis de Calidad</div>
            </div>
            <div class="panel-body">
                @if($lastQuality)
                <div style="margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-size:12px;color:#94a3b8">Entrega #{{ $lastQuality->milkDelivery?->id }}</div>
                        <div style="font-weight:700">{{ $lastQuality->milkDelivery?->delivery_date?->format('d/m/Y') }}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:28px;font-weight:900;color:{{ $lastQuality->qualityScore() >= 80 ? '#059669' : ($lastQuality->qualityScore() >= 60 ? '#d97706' : '#dc2626') }}">{{ $lastQuality->qualityScore() }}%</div>
                        <span class="badge {{ $lastQuality->result === 'aprobado' ? 'badge-green' : ($lastQuality->result === 'rechazado' ? 'badge-red' : 'badge-amber') }}">{{ $lastQuality->result }}</span>
                    </div>
                </div>
                <div class="progress-wrap" style="height:10px">
                    <div class="progress {{ $lastQuality->qualityScore() >= 80 ? '' : ($lastQuality->qualityScore() >= 60 ? 'amber' : 'red') }}" style="width:{{ $lastQuality->qualityScore() }}%"></div>
                </div>
                @else
                <div class="empty"><div class="empty-icon">🧪</div><h3>Sin análisis aún</h3></div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Mis Últimas Entregas</div>
        <a href="{{ route('producer.deliveries') }}" class="btn btn-sm btn-ghost">Historial completo →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Litros</th><th>Precio/L</th><th>Total</th><th>Acopiador</th><th>Calidad</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($deliveries ?? [] as $d)
                <tr>
                    <td style="font-weight:600">{{ $d->delivery_date?->format('d/m/Y') }}</td>
                    <td style="font-weight:700">{{ $d->liters }} L</td>
                    <td>S/ {{ number_format($d->price_per_liter, 2) }}</td>
                    <td style="font-weight:700;color:#059669">S/ {{ number_format($d->total_amount, 2) }}</td>
                    <td>{{ $d->collector?->fullname ?? '-' }}</td>
                    <td>
                        @if($d->qualityReport) <span class="badge badge-green">{{ $d->qualityReport->qualityScore() }}%</span>
                        @else <span class="badge badge-gray">Pendiente</span> @endif
                    </td>
                    <td><span class="badge {{ $d->status === 'rechazado' ? 'badge-red' : ($d->status === 'registrado' ? 'badge-amber' : 'badge-green') }}">{{ $d->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">🚛</div><h3>Sin entregas registradas</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('chartProd'), {
    type: 'line',
    data: {
        labels: @json($chartLabels ?? []),
        datasets: [{
            label: 'Litros entregados',
            data: @json($chartLiters ?? []),
            borderColor: '#0891b2',
            backgroundColor: 'rgba(8, 145, 178, 0.12)',
            fill: true, tension: 0.4, borderWidth: 3, pointRadius: 3, pointBackgroundColor: '#0e7490'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
});
</script>
@endsection
