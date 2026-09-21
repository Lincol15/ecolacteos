@extends('layouts.app')

@section('page-title', 'Historial de Jornada')
@section('page-subtitle', 'Actividad de hoy: ' . now()->format('d/m/Y'))

@section('top-actions')
    <a href="{{ route('collector.delivery-create') }}" class="btn btn-primary">➕ Nueva Entrega</a>
@endsection

@section('content')
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">🥛</div>
        <div class="stat-label">Litros Recolectados Hoy</div>
        <div class="stat-value green">{{ number_format($totals['liters'], 2) }} L</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💲</div>
        <div class="stat-label">Monto Total</div>
        <div class="stat-value amber">S/ {{ number_format($totals['amount'], 2) }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📋</div>
        <div class="stat-label">Entregas Registradas</div>
        <div class="stat-value blue">{{ $totals['count'] }}</div>
    </div>
</div>

@if($myRoute)
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🗺️ Ruta de Hoy: {{ $myRoute->name }}</div>
        <a href="{{ route('collector.route-show', $myRoute) }}" class="btn btn-sm btn-ghost">Ver ruta completa</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Productor</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach($myRoute->stops as $stop)
                <tr>
                    <td>{{ $stop->stop_order }}</td>
                    <td>{{ $stop->producer?->user?->fullname ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ match($stop->status) { 'visitado' => 'badge-green', 'rechazado' => 'badge-red', 'ausente' => 'badge-gray', 'en_camino' => 'badge-blue', default => 'badge-amber' } }}">
                            {{ ucfirst(str_replace('_', ' ', $stop->status)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Entregas Registradas Hoy</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Hora</th><th>Productor</th><th>Litros</th><th>Total</th><th>Calidad</th></tr></thead>
            <tbody>
                @forelse($todayDeliveries as $d)
                <tr>
                    <td>{{ $d->delivery_time?->format('H:i') ?? '-' }}</td>
                    <td>{{ $d->producer?->user?->fullname ?? 'N/A' }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                    <td>S/ {{ number_format($d->total_amount, 2) }}</td>
                    <td>
                        @if($d->qualityReport)
                        <span class="badge {{ $d->qualityReport->result === 'aprobado' ? 'badge-green' : 'badge-amber' }}">{{ $d->qualityReport->qualityScore() }}%</span>
                        @else
                        <span class="badge badge-gray">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty"><div class="empty-icon">📋</div><h3>Sin entregas registradas hoy</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
