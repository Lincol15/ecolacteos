@extends('layouts.app')

@section('page-title', 'Gestión de Usuarios')
@section('page-subtitle', 'Administra los usuarios del sistema')

@section('top-actions')
    <a href="{{ route('admin.users-create') }}" class="btn btn-primary">
        ➕ Nuevo Usuario
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Usuarios</div>
        <div class="stat-value green">{{ $users->count() }}</div>
        <div class="stat-icon-wrap green">👥</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Productores</div>
        <div class="stat-value amber">{{ $users->where('role', 'productor')->count() }}</div>
        <div class="stat-icon-wrap amber">👨‍🌾</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Acopiadores</div>
        <div class="stat-value blue">{{ $users->where('role', 'acopiador')->count() }}</div>
        <div class="stat-icon-wrap blue">🚛</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Activos</div>
        <div class="stat-value purple">{{ $users->where('active', true)->count() }}</div>
        <div class="stat-icon-wrap purple">✅</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Listado de Usuarios</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.users') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Filtrar por Rol</label>
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos los roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="gerente" {{ request('role') == 'gerente' ? 'selected' : '' }}>Gerente</option>
                    <option value="acopiador" {{ request('role') == 'acopiador' ? 'selected' : '' }}>Acopiador</option>
                    <option value="control_calidad" {{ request('role') == 'control_calidad' ? 'selected' : '' }}>Control Calidad</option>
                    <option value="trabajador_planta" {{ request('role') == 'trabajador_planta' ? 'selected' : '' }}>Trabajador Planta</option>
                    <option value="productor" {{ request('role') == 'productor' ? 'selected' : '' }}>Productor</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nombre o email...">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>DNI</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }} {{ $user->lastname }}</strong>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->dni ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ match($user->role) {
                                'admin' => 'purple',
                                'gerente' => 'blue',
                                'acopiador' => 'amber',
                                'control_calidad' => 'cyan',
                                'trabajador_planta' => 'green',
                                'productor' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($user->role) {
                                    'admin' => '👑 Admin',
                                    'gerente' => '📊 Gerente',
                                    'acopiador' => '🚛 Acopiador',
                                    'control_calidad' => '🧪 Control Calidad',
                                    'trabajador_planta' => '🏭 Trab. Planta',
                                    'productor' => '👨‍🌾 Productor',
                                    default => ucfirst($user->role)
                                } }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $user->active ? 'badge-green' : 'badge-red' }}">
                                {{ $user->active ? '✅ Activo' : '❌ Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <a href="{{ route('admin.users-edit', $user) }}" class="btn btn-info btn-sm">✏️ Editar</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div class="empty-icon">👥</div>
                                <h3>No hay usuarios registrados</h3>
                                <p>Intenta ajustar los filtros o crea un nuevo usuario.</p>
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
