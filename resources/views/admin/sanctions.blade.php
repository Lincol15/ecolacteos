@extends('layouts.app')

@section('page-title', 'Gestión de Sanciones')
@section('page-subtitle', 'Sanciones aplicadas a productores por incumplimientos')

@section('top-actions')
    <a href="{{ route('admin.sanctions-create') }}" class="btn btn-primary">
        <span>➕</span> Nueva Sanción
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card red">
        <div class="stat-label">Activas</div>
        <div class="stat-value red">{{ $sanctions->where('status', 'activa')->count() }}</div>
        <div class="stat-icon-wrap red">🚫</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Cumplidas</div>
        <div class="stat-value green">{{ $sanctions->where('status', 'cumplida')->count() }}</div>
        <div class="stat-icon-wrap green">✅</div>
    </div>
    <div class="stat-card gray">
        <div class="stat-label">Anuladas</div>
        <div class="stat-value" style="color:#475569">{{ $sanctions->where('status', 'anulada')->count() }}</div>
        <div class="stat-icon-wrap" style="background:#f1f5f9;color:#475569">⚫</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚫 Registro de Sanciones</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.sanctions') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="activa" {{ request('status') == 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="cumplida" {{ request('status') == 'cumplida' ? 'selected' : '' }}>Cumplida</option>
                    <option value="anulada" {{ request('status') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.sanctions') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Productor</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Emitida por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanctions as $s)
                    <tr>
                        <td>{{ $s->sanction_date?->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $s->producer?->user?->fullname ?? 'N/A' }}</strong>
                            <div style="font-size:11px;color:var(--text-light)">{{ $s->producer?->code }}</div>
                        </td>
                        <td>{{ \App\Models\Sanction::TYPES[$s->type] ?? $s->type }}</td>
                        <td>{{ $s->motivo }}</td>
                        <td>{{ $s->amount ? 'S/ ' . number_format($s->amount, 2) : '—' }}</td>
                        <td>
                            <span class="badge badge-{{ match($s->status) { 'activa' => 'red', 'cumplida' => 'green', default => 'gray' } }}">
                                {{ \App\Models\Sanction::STATUS[$s->status] ?? $s->status }}
                            </span>
                        </td>
                        <td>{{ $s->issuedBy?->fullname ?? '—' }}</td>
                        <td>
                            @if($s->status === 'activa')
                            <form method="POST" action="{{ route('admin.sanction-update', $s) }}" style="display:inline-flex;gap:6px">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="cumplida">
                                <button type="submit" class="btn btn-sm btn-primary">✅ Marcar Cumplida</button>
                            </form>
                            <form method="POST" action="{{ route('admin.sanction-update', $s) }}" style="display:inline-flex;gap:6px">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="anulada">
                                <button type="submit" class="btn btn-sm btn-ghost">✖ Anular</button>
                            </form>
                            @else
                            <span class="badge badge-gray">Sin acciones</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">🚫</div>
                                <h3>No hay sanciones registradas</h3>
                                <p>Cuando se apliquen sanciones a productores aparecerán aquí.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($sanctions, 'links'))
        <div style="padding:20px">{{ $sanctions->links() }}</div>
        @endif
    </div>
</div>
@endsection
