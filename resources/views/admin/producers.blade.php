@extends('layouts.app')

@section('page-title', 'Productores Lácteos')
@section('page-subtitle', 'Administra los productores de leche del sistema')

@section('top-actions')
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.producers-create') }}" class="btn btn-primary">
        ➕ Nuevo Productor
    </a>
    @endif
    <a href="{{ route('admin.producers') }}" class="btn btn-accent">
        📊 Exportar CSV
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Productores</div>
        <div class="stat-value green">{{ $counts['total'] }}</div>
        <div class="stat-icon-wrap green">👨‍🌾</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Activos</div>
        <div class="stat-value blue">{{ $counts['activos'] }}</div>
        <div class="stat-icon-wrap blue">✅</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Inactivos</div>
        <div class="stat-value amber">{{ $counts['inactivos'] }}</div>
        <div class="stat-icon-wrap amber">❌</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🏡 Listado de Productores</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.producers') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Zona / Región</label>
                <select name="zone" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas las zonas</option>
                    @foreach($zones as $zone)
                    <option value="{{ $zone }}" {{ request('zone') == $zone ? 'selected' : '' }}>{{ $zone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Comunidad</label>
                <select name="comunidad" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas las comunidades</option>
                    @foreach($comunidades as $comunidad)
                    <option value="{{ $comunidad }}" {{ request('comunidad') == $comunidad ? 'selected' : '' }}>{{ $comunidad }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="activo" {{ request('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nombre, DNI, teléfono o comunidad...">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.producers') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Productor / Finca</th>
                        <th>Zona / Comunidad</th>
                        <th>Vacas</th>
                        <th>Litros/Día</th>
                        <th>Total Litros Mes</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($producers as $producer)
                    <tr>
                        <td><code class="badge badge-blue">{{ $producer->code }}</code></td>
                        <td>
                            <strong>{{ $producer->user->name ?? 'Sin usuario' }} {{ $producer->user->lastname ?? '' }}</strong>
                            <div style="font-size:12px; color:var(--text-light);">{{ $producer->farm_name }}</div>
                        </td>
                        <td>
                            <strong>{{ $producer->zone }}</strong>
                            <div style="font-size:12px; color:var(--text-light);">{{ $producer->comunidad }}</div>
                        </td>
                        <td>{{ number_format($producer->cows_count, 0) }} 🐄</td>
                        <td>{{ number_format($producer->daily_avg_liters, 1) }} L</td>
                        <td><strong class="text-green">{{ number_format($producer->daily_avg_liters * 30, 0) }} L</strong></td>
                        <td>
                            <span class="badge {{ $producer->status == 'activo' ? 'badge-green' : 'badge-red' }}">
                                {{ $producer->status == 'activo' ? '✅ Activo' : '❌ Inactivo' }}
                            </span>
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.producers-edit', $producer) }}" class="btn btn-info btn-sm">✏️ Editar</a>
                            <form method="POST" action="{{ route('admin.producer-toggle-status', $producer) }}" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm {{ $producer->status == 'activo' ? 'btn-danger' : 'btn-primary' }}">
                                    {{ $producer->status == 'activo' ? '❌ Desactivar' : '✅ Activar' }}
                                </button>
                            </form>
                            @else
                            <span class="badge badge-gray">Solo lectura</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">👨‍🌾</div>
                                <h3>No hay productores registrados</h3>
                                <p>Agrega productores desde la sección de usuarios o ajusta los filtros.</p>
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
