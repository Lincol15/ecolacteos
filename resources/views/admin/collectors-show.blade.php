@extends('layouts.app')

@section('page-title', $collector->fullname)
@section('page-subtitle', 'Detalle de acopiador')

@section('top-actions')
    <a href="{{ route('admin.collectors') }}" class="btn btn-ghost">← Volver a Acopiadores</a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Litros Hoy</div>
        <div class="stat-value green">{{ number_format($todayDeliveries->sum('liters'), 1) }} L</div>
        <div class="stat-icon-wrap green">🥛</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Entregas Hoy</div>
        <div class="stat-value blue">{{ $todayDeliveries->count() }}</div>
        <div class="stat-icon-wrap blue">📋</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Productores Asignados</div>
        <div class="stat-value purple">{{ $assignedProducers->count() }}</div>
        <div class="stat-icon-wrap purple">👨‍🌾</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Entregas de Hoy</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Hora</th><th>Productor</th><th>Litros</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($todayDeliveries as $d)
                <tr>
                    <td>{{ $d->delivery_time?->format('H:i') ?? '-' }}</td>
                    <td>{{ $d->producer?->user?->fullname ?? 'N/A' }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                    <td><span class="badge badge-blue">{{ ucfirst($d->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty"><div class="empty-icon">🚛</div><h3>Sin entregas hoy</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👨‍🌾 Productores Asignados</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Código</th><th>Productor</th><th>Zona</th></tr></thead>
                <tbody>
                    @forelse($assignedProducers as $p)
                    <tr>
                        <td><code class="badge badge-blue">{{ $p->code }}</code></td>
                        <td>{{ $p->user?->fullname ?? 'N/A' }}</td>
                        <td>{{ $p->zone }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="empty"><div class="empty-icon">👨‍🌾</div><h3>Sin productores asignados</h3><p>Asigna productores creando o editando una ruta de acopio.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🗺️ Rutas de Acopio</div>
            <a href="{{ route('admin.routes-create') }}" class="btn btn-sm btn-primary">➕ Nueva Ruta</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ruta</th><th>Día</th><th>Paradas</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse($routes as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><div style="font-size:11px;color:var(--text-light)">{{ $r->code }}</div></td>
                        <td>{{ $r->day }}</td>
                        <td>{{ $r->stops->count() }}</td>
                        <td><span class="badge badge-blue">{{ ucfirst($r->status) }}</span></td>
                        <td><a href="{{ route('admin.routes-edit', $r) }}" class="btn btn-sm btn-ghost">✏️ Asignar</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty"><div class="empty-icon">🗺️</div><h3>Sin rutas asignadas</h3></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
