@extends('layouts.app')

@section('title', 'Detalle Ruta - Ecolácteos Huata')
@section('page-title', 'Detalle de Ruta')
@section('page-subtitle', 'Gestión de paradas y entregas de la ruta')

@section('top-actions')
    <a href="{{ route('collector.routes') }}" class="btn btn-ghost">← Volver a Rutas</a>
@endsection

@section('content')
@if(isset($route))
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">🗺️</div>
        <div class="stat-label">Ruta</div>
        <div class="stat-value blue" style="font-size:20px">{{ $route->name ?? 'Ruta ' . $route->id }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">📅</div>
        <div class="stat-label">Día Asignado</div>
        <div class="stat-value amber">{{ $route->day ?? '-' }}</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">📍</div>
        <div class="stat-label">Total Paradas</div>
        <div class="stat-value" style="color:#0891b2">{{ count($route->stops ?? []) }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon-wrap green">✅</div>
        <div class="stat-label">Avance</div>
        <div class="stat-value green">
            {{ count($route->stops ?? []) > 0 ? collect($route->stops ?? [])->filter(fn($s) => $s->status === 'completado')->count() : 0 }}
            / {{ count($route->stops ?? []) }}
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📍 Lista de Paradas</div>
        <div style="display:flex;gap:8px">
            <span class="badge badge-amber">⏳ Pendiente: {{ collect($route->stops ?? [])->where('status','pendiente')->count() }}</span>
            <span class="badge badge-green">✅ Completadas: {{ collect($route->stops ?? [])->where('status','completado')->count() }}</span>
        </div>
    </div>
    <div class="panel-body" style="padding:0">
        <table>
            <thead><tr>
                <th>#</th>
                <th>Productor</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($route->stops ?? [] as $stop)
                <tr>
                    <td style="font-weight:800;color:#0891b2;font-size:16px">{{ $loop->iteration }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#d1fae5,#a7f3d0);display:flex;align-items:center;justify-content:center;font-size:18px">👨‍🌾</div>
                            <div>
                                <div style="font-weight:700">{{ $stop->producer?->user?->fullname ?? 'Productor' }}</div>
                                <div style="font-size:11px;color:#94a3b8">{{ $stop->producer?->code ?? 'Sin código' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $stop->address ?? $stop->producer?->location ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('collector.route-update-stop', $stop->id) }}" style="display:inline-flex;gap:6px;align-items:center;flex-wrap:wrap">
                            @csrf
                            @method('PUT')
                            <select name="status" class="form-select" style="padding:6px 10px;font-size:12px;width:auto" onchange="this.form.submit()">
                                <option value="pendiente" {{ $stop->status === 'pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                                <option value="en_curso" {{ $stop->status === 'en_curso' ? 'selected' : '' }}>🚛 En Curso</option>
                                <option value="completado" {{ $stop->status === 'completado' ? 'selected' : '' }}>✅ Completado</option>
                                <option value="cancelado" {{ $stop->status === 'cancelado' ? 'selected' : '' }}>❌ Cancelado</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        @if($stop->status !== 'completado' && $stop->status !== 'cancelado')
                        <a href="{{ route('collector.delivery-create') }}?route_stop_id={{ $stop->id }}&collection_route_id={{ $route->id }}" class="btn btn-sm btn-accent">
                            ➕ Registrar Entrega
                        </a>
                        @else
                        <span class="badge badge-gray">Finalizada</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty"><div class="empty-icon">📍</div><h3>Sin paradas en esta ruta</h3><p>No hay paradas programadas para esta ruta.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Entregas Realizadas en esta Ruta</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Productor</th><th>Litros</th><th>Temperatura</th><th>Monto</th><th>Fecha</th><th>Estado</th>
            </tr></thead>
            <tbody>
                @forelse($routeDeliveries ?? [] as $d)
                <tr>
                    <td style="font-weight:600">{{ $d->producer?->user?->fullname ?? '-' }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                    <td>{{ $d->temperature ?? '-' }}°C</td>
                    <td style="font-weight:700;color:#d97706">S/ {{ number_format($d->total_amount ?? ($d->liters * $d->price_per_liter), 2) }}</td>
                    <td style="font-size:12px;color:#64748b">{{ $d->delivery_date?->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $d->status === 'aceptado' || $d->status === 'analizado' ? 'badge-green' : ($d->status === 'rechazado' ? 'badge-red' : 'badge-amber') }}">
                            {{ ucfirst($d->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty" style="padding:30px 20px"><div class="empty-icon" style="font-size:40px">🚛</div><h3 style="font-size:15px">Sin entregas registradas aún</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-body">
        <div class="empty">
            <div class="empty-icon">🗺️</div>
            <h3>Ruta no encontrada</h3>
            <p>La ruta solicitada no existe.</p>
            <a href="{{ route('collector.routes') }}" class="btn btn-primary" style="margin-top:16px">← Volver a Rutas</a>
        </div>
    </div>
</div>
@endif
@endsection
