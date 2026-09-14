@extends('layouts.app')

@section('page-title', 'Gestión de Avisos / Notificaciones')
@section('page-subtitle', 'Comunicados para usuarios del sistema')

@section('top-actions')
    <a href="{{ route('admin.notifications-create') }}" class="btn btn-primary">
        ➕ Nuevo Aviso
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Avisos Activos</div>
        <div class="stat-value green">{{ $notifications->where('is_active', true)->count() }}</div>
        <div class="stat-icon-wrap green">✅</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Prioridad Alta/Urgente</div>
        <div class="stat-value amber">{{ $notifications->whereIn('priority', ['alta','urgente'])->count() }}</div>
        <div class="stat-icon-wrap amber">🚨</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Dirigido a Productores</div>
        <div class="stat-value blue">{{ $notifications->where('target', 'productores')->count() + $notifications->where('target', 'todos')->count() }}</div>
        <div class="stat-icon-wrap blue">👨‍🌾</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Creados este Mes</div>
        <div class="stat-value purple">{{ $notifications->count() }}</div>
        <div class="stat-icon-wrap purple">📊</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📢 Avisos y Comunicados</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.notifications') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Destino</label>
                <select name="target" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="todos" {{ request('target') == 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="productores" {{ request('target') == 'productores' ? 'selected' : '' }}>Productores</option>
                    <option value="acopiadores" {{ request('target') == 'acopiadores' ? 'selected' : '' }}>Acopiadores</option>
                    <option value="gerencia" {{ request('target') == 'gerencia' ? 'selected' : '' }}>Gerencia</option>
                    <option value="admin" {{ request('target') == 'admin' ? 'selected' : '' }}>Solo Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Prioridad</label>
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <option value="urgente" {{ request('priority') == 'urgente' ? 'selected' : '' }}>🚨 Urgente</option>
                    <option value="alta" {{ request('priority') == 'alta' ? 'selected' : '' }}>🔴 Alta</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>🟡 Normal</option>
                    <option value="baja" {{ request('priority') == 'baja' ? 'selected' : '' }}>🟢 Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="active" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.notifications') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Destino</th>
                        <th>Prioridad</th>
                        <th>Activo</th>
                        <th>Publicado</th>
                        <th>Creado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $n)
                    <tr>
                        <td>
                            <strong>{{ $n->title }}</strong>
                            <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($n->message), 60) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($n->target) {
                                'todos' => 'purple',
                                'productores' => 'green',
                                'acopiadores' => 'amber',
                                'gerencia' => 'blue',
                                'admin' => 'red',
                                default => 'gray'
                            } }}">
                                {{ match($n->target) {
                                    'todos' => '👥 Todos',
                                    'productores' => '👨‍🌾 Productores',
                                    'acopiadores' => '🚛 Acopiadores',
                                    'gerencia' => '👔 Gerencia',
                                    'admin' => '👑 Admin',
                                    default => ucfirst($n->target)
                                } }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($n->priority) {
                                'urgente' => 'red', 'alta' => 'amber', 'normal' => 'blue', 'baja' => 'gray', default => 'gray'
                            } }}">
                                {{ match($n->priority) {
                                    'urgente' => '🚨 Urgente',
                                    'alta' => '🔴 Alta',
                                    'normal' => '🟡 Normal',
                                    'baja' => '🟢 Baja',
                                    default => ucfirst($n->priority)
                                } }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $n->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $n->is_active ? '✅ Activo' : '❌ Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $n->published_at ? \Carbon\Carbon::parse($n->published_at)->format('d/m/Y') : '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">
                                Expira: {{ $n->expires_at ? \Carbon\Carbon::parse($n->expires_at)->format('d/m/Y') : 'Sin venc.' }}
                            </div>
                        </td>
                        <td>{{ $n->createdBy->name ?? 'Sistema' }} {{ $n->createdBy->lastname ?? '' }}</td>
                        <td>
                            <button class="btn btn-info btn-sm">🔍 Ver</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <div class="empty-icon">📢</div>
                                <h3>No hay avisos creados</h3>
                                <p>Crea tu primer aviso presionando "Nuevo Aviso".</p>
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
