@extends('layouts.app')

@section('page-title', 'Gestión de Reclamos')
@section('page-subtitle', 'Seguimiento y atención de reclamos de productores')

@section('top-actions')
    <span class="badge badge-red">{{ $complaints->where('status', 'pendiente')->count() }} Pendientes</span>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card red">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value red">{{ $complaints->where('status', 'pendiente')->count() }}</div>
        <div class="stat-icon-wrap red">🔴</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">En Proceso</div>
        <div class="stat-value amber">{{ $complaints->where('status', 'proceso')->count() }}</div>
        <div class="stat-icon-wrap amber">🟠</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Resueltos</div>
        <div class="stat-value green">{{ $complaints->where('status', 'resuelto')->count() }}</div>
        <div class="stat-icon-wrap green">🟢</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Prioridad Alta</div>
        <div class="stat-value purple">{{ $complaints->where('priority', 'alta')->count() + $complaints->where('priority', 'urgente')->count() }}</div>
        <div class="stat-icon-wrap purple">🚨</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📩 Bandeja de Reclamos</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.complaints') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Categoría</label>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @forelse(($complaints->pluck('category')->unique()->filter() ?? []) as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @empty
                    @endforelse
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
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso" {{ request('status') == 'proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="resuelto" {{ request('status') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado" {{ request('status') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.complaints') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Fecha</th>
                        <th>Productor</th>
                        <th>Categoría</th>
                        <th>Asunto</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                    <tr>
                        <td><code class="badge badge-blue">{{ $c->ticket_number }}</code></td>
                        <td>
                            <strong>{{ $c->created_at ? $c->created_at->format('d/m/Y') : '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $c->created_at ? $c->created_at->format('H:i') : '' }}</div>
                        </td>
                        <td>
                            <strong>{{ $c->producer->user->name ?? 'Sin productor' }} {{ $c->producer->user->lastname ?? '' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $c->producer->code ?? '' }}</div>
                        </td>
                        <td>{{ $c->category }}</td>
                        <td><strong>{{ $c->subject }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($c->priority) {
                                'urgente' => 'red',
                                'alta' => 'amber',
                                'normal' => 'blue',
                                'baja' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($c->priority) {
                                    'urgente' => '🚨 Urgente',
                                    'alta' => '🔴 Alta',
                                    'normal' => '🟡 Normal',
                                    'baja' => '🟢 Baja',
                                    default => ucfirst($c->priority)
                                } }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($c->status) {
                                'pendiente' => 'red',
                                'proceso' => 'amber',
                                'resuelto' => 'green',
                                'cerrado' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($c->status) {
                                    'pendiente' => '🔴 Pendiente',
                                    'proceso' => '🟠 Proceso',
                                    'resuelto' => '🟢 Resuelto',
                                    'cerrado' => '⚫ Cerrado',
                                    default => ucfirst($c->status)
                                } }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.complaints-show', $c) }}" class="btn btn-info btn-sm">🔍 Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">📩</div>
                                <h3>No hay reclamos registrados</h3>
                                <p>Cuando los productores registren reclamos aparecerán aquí.</p>
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
