@extends('layouts.app')

@section('page-title', 'Rutas de Acopio')
@section('page-subtitle', 'Planificación y gestión de rutas de recolección de leche')

@section('top-actions')
    <a href="{{ route('admin.routes-create') }}" class="btn btn-primary">
        ➕ Nueva Ruta
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Rutas Activas</div>
        <div class="stat-value green">{{ $routes->where('status', 'activa')->count() }}</div>
        <div class="stat-icon-wrap green">🗺️</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Total Paradas</div>
        <div class="stat-value blue">{{ $routes->sum('stops_count') }}</div>
        <div class="stat-icon-wrap blue">📍</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Dist. Estimada</div>
        <div class="stat-value amber">{{ number_format($routes->sum('estimated_distance_km'), 1 }} km</div>
        <div class="stat-icon-wrap amber">🛣️</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Acopiadores Asignados</div>
        <div class="stat-value cyan">{{ $routes->pluck('collector_id')->filter()->unique()->count() }}</div>
        <div class="stat-icon-wrap cyan">🚛</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🗺️ Listado de Rutas</div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Ruta</th>
                        <th>Día</th>
                        <th>Paradas</th>
                        <th>Acopiador</th>
                        <th>Horario</th>
                        <th>Vehículo</th>
                        <th>Dist.</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $r)
                    <tr>
                        <td><code class="badge badge-blue">{{ $r->code }}</code></td>
                        <td><strong>{{ $r->name }}</strong>
                            @if($r->description)
                                <div style="font-size:11px; color:var(--text-light);">{{ $r->description }}</div>
                            @endif
                        </td>
                        <td>{{ $r->day }}</td>
                        <td><span class="badge badge-cyan">{{ $r->stops_count ?? 0 }} 📍</span></td>
                        <td>{{ $r->collector->name ?? '—' }} {{ $r->collector->lastname ?? '' }}</td>
                        <td>
                            <span class="badge badge-gray">🕐 {{ $r->start_time }} - {{ $r->end_time }}</span>
                        </td>
                        <td><span class="badge badge-amber">🚚 {{ $r->vehicle_plate ?? '—' }}</span></td>
                        <td>{{ number_format($r->estimated_distance_km ?? 0, 1) }} km</td>
                        <td>
                            <span class="badge {{ $r->status == 'activa' ? 'badge-green' : 'badge-red' }}">
                                {{ $r->status == 'activa' ? '✅ Activa' : '❌ Inactiva' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.routes-edit', $r) }}" class="btn btn-info btn-sm">✏️ Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty">
                                <div class="empty-icon">🗺️</div>
                                <h3>No hay rutas registradas</h3>
                                <p>Crea una nueva ruta presionando el botón "Nueva Ruta".</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
