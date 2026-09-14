@extends('layouts.app')

@section('title', 'Dashboard - VACA SYS')
@section('page-title', 'Panel de Control')
@section('page-subtitle', 'Vista general de operaciones lácteas en tiempo real')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">🥛</div>
        <div class="stat-label">Leche Recolectada Hoy</div>
        <div class="stat-value green">{{ number_format($stats['liters_today'] ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">📅</div>
        <div class="stat-label">Total del Mes</div>
        <div class="stat-value amber">{{ number_format($stats['liters_month'] ?? 0, 2) }} L</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">👨‍🌾</div>
        <div class="stat-label">Productores Activos</div>
        <div class="stat-value blue">{{ $stats['producers_active'] ?? 0 }}</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon-wrap purple">🏭</div>
        <div class="stat-label">Lotes en Proceso</div>
        <div class="stat-value purple">{{ $stats['batches_active'] ?? 0 }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon-wrap red">💰</div>
        <div class="stat-label">Ventas de Hoy</div>
        <div class="stat-value red">S/ {{ number_format($stats['sales_today'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">💲</div>
        <div class="stat-label">Ventas del Mes</div>
        <div class="stat-value" style="color:#0891b2">S/ {{ number_format($stats['sales_month'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon-wrap red">📩</div>
        <div class="stat-label">Reclamos Abiertos</div>
        <div class="stat-value red">{{ $stats['complaints_open'] ?? 0 }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💹</div>
        <div class="stat-label">Precio / Litro Leche</div>
        <div class="stat-value amber">S/ {{ number_format($stats['milk_price'] ?? 0, 2) }}</div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📈 Recolección Últimos 30 Días</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <canvas id="chartCollection"></canvas>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📊 Ventas Últimos 6 Meses</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <canvas id="chartSales"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚛 Entregas Recientes</div>
            <a href="{{ route('admin.deliveries') }}" class="btn btn-sm btn-ghost">Ver todas →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Productor</th><th>Litros</th><th>Estado</th><th>Calidad</th><th>Fecha</th>
                </tr></thead>
                <tbody>
                    @forelse($recentDeliveries ?? [] as $d)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $d->producer?->user?->fullname ?? 'N/A' }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $d->producer?->code ?? '' }}</div>
                        </td>
                        <td style="font-weight:700">{{ number_format($d->liters, 2) }} L</td>
                        <td>
                            @if($d->status === 'aceptado' || $d->status === 'analizado') <span class="badge badge-green">● {{ $d->status }}</span>
                            @elseif($d->status === 'rechazado') <span class="badge badge-red">● {{ $d->status }}</span>
                            @elseif($d->status === 'registrado') <span class="badge badge-amber">● {{ $d->status }}</span>
                            @else <span class="badge badge-gray">{{ $d->status }}</span> @endif
                        </td>
                        <td>
                            @if($d->qualityReport) 
                                <span class="badge {{ $d->qualityReport->result === 'aprobado' ? 'badge-green' : ($d->qualityReport->result === 'rechazado' ? 'badge-red' : 'badge-amber') }}">
                                    {{ $d->qualityReport->qualityScore() }}%
                                </span>
                            @else <span class="badge badge-gray">Pendiente</span> @endif
                        </td>
                        <td style="font-size:12px;color:#64748b">{{ $d->delivery_date?->format('d/m') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">🚛</div><h3>Sin entregas registradas</h3><p>Aún no hay registros de entrega.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🏆 Top Productores del Mes</div>
        </div>
        <div class="panel-body" style="padding-bottom:8px">
            @forelse($milkByProductor ?? [] as $idx => $p)
            <div style="padding:11px 0; border-bottom:1px solid #f1f5f9">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="width:26px;height:26px;border-radius:50%;background:{{ $idx === 0 ? 'linear-gradient(135deg,#fbbf24,#f59e0b)' : ($idx === 1 ? 'linear-gradient(135deg,#94a3b8,#64748b)' : ($idx === 2 ? 'linear-gradient(135deg,#fb923c,#ea580c)' : '#e2e8f0')) }};color:{{ $idx < 3 ? '#fff' : '#64748b' }};font-weight:700;font-size:12px;display:flex;align-items:center;justify-content:center">{{ $idx + 1 }}</span>
                        <div>
                            <div style="font-weight:600;font-size:13.5px">{{ $p->user?->fullname ?? 'N/A' }}</div>
                            <div style="font-size:11px;color:#94a3b8">{{ $p->code }}</div>
                        </div>
                    </div>
                    <div style="font-weight:800;color:#059669">{{ number_format($p->month_liters ?? 0, 1) }} L</div>
                </div>
                <div class="progress-wrap" style="height:6px">
                    <div class="progress" style="width:{{ $milkByProductor->max('month_liters') > 0 ? ($p->month_liters / $milkByProductor->max('month_liters') * 100) : 0 }}%"></div>
                </div>
            </div>
            @empty
            <div class="empty"><div class="empty-icon">🏆</div><h3>Sin datos</h3></div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">💹 Ventas Recientes</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead>
                <tbody>
                    @forelse($recentSales ?? [] as $s)
                    <tr>
                        <td style="font-weight:600;color:#2563eb">{{ $s->invoice_number }}</td>
                        <td>{{ $s->client_name }}</td>
                        <td style="font-weight:700;color:#059669">S/ {{ number_format($s->total_amount, 2) }}</td>
                        <td>
                            @if($s->payment_status === 'pagado') <span class="badge badge-green">Pagado</span>
                            @elseif($s->payment_status === 'pendiente') <span class="badge badge-amber">Pendiente</span>
                            @elseif($s->payment_status === 'parcial') <span class="badge badge-blue">Parcial</span>
                            @else <span class="badge badge-red">Anulado</span> @endif
                        </td>
                        <td style="font-size:12px;color:#64748b">{{ $s->sale_date?->format('d/m') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">💹</div><h3>Sin ventas</h3></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="panel" style="margin-bottom:24px">
            <div class="panel-header">
                <div class="panel-title">📢 Avisos Importantes</div>
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
                <div class="empty"><div class="empty-icon">📢</div><h3>No hay avisos</h3></div>
                @endforelse
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📩 Reclamos Abiertos</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Ticket</th><th>Asunto</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse($openComplaints ?? [] as $c)
                        <tr>
                            <td style="font-weight:600;color:#7c3aed">{{ $c->ticket_number }}</td>
                            <td style="font-size:13px">{{ \Illuminate\Support\Str::limit($c->subject, 30) }}</td>
                            <td><span class="badge {{ $c->status === 'abierto' ? 'badge-amber' : 'badge-blue' }}">{{ $c->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty" style="padding:30px 20px"><h3>Sin reclamos abiertos ✅</h3></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartCollection'), {
        type: 'line',
        data: {
            labels: @json($collectionLabels ?? []),
            datasets: [{
                label: 'Litros recolectados',
                data: @json($collectionLiters ?? []),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 3,
                pointBackgroundColor: '#059669'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
    new Chart(document.getElementById('chartSales'), {
        type: 'bar',
        data: {
            labels: @json($salesMonthly ?? []),
            datasets: [{
                label: 'Ventas S/',
                data: @json($salesAmounts ?? []),
                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                borderRadius: 10,
                borderSkipped: false
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
});
</script>
@endsection
