@extends('layouts.app')

@section('page-title', 'Acopiadores')
@section('page-subtitle', 'Equipo de recolección de leche en campo')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Acopiadores</div>
        <div class="stat-value green">{{ $collectors->count() }}</div>
        <div class="stat-icon-wrap green">🚜</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Litros Recolectados Hoy</div>
        <div class="stat-value blue">{{ number_format($collectors->sum('today_liters'), 1) }} L</div>
        <div class="stat-icon-wrap blue">🥛</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Entregas Registradas Hoy</div>
        <div class="stat-value amber">{{ number_format($collectors->sum('today_deliveries'), 0) }}</div>
        <div class="stat-icon-wrap amber">📋</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚜 Listado de Acopiadores</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Acopiador</th>
                    <th>Teléfono</th>
                    <th>Vehículo</th>
                    <th>Litros Hoy</th>
                    <th>Entregas Hoy</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collectors as $c)
                <tr>
                    <td><strong>{{ $c->fullname }}</strong></td>
                    <td>{{ $c->phone ?? '-' }}</td>
                    <td>{{ $c->vehiculo ?? '-' }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($c->today_liters ?? 0, 1) }} L</td>
                    <td>{{ $c->today_deliveries ?? 0 }}</td>
                    <td>
                        <span class="badge {{ $c->active ? 'badge-green' : 'badge-red' }}">{{ $c->active ? '✅ Activo' : '❌ Inactivo' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.collectors-show', $c) }}" class="btn btn-info btn-sm">👁️ Ver detalle</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <div class="empty-icon">🚜</div>
                            <h3>No hay acopiadores registrados</h3>
                            <p>Crea usuarios con rol "Acopiador" desde la sección de Usuarios.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
