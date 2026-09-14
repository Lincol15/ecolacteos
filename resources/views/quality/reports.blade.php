@extends('layouts.app')

@section('title', 'Reportes de Calidad - VACA SYS')
@section('page-title', 'Reportes de Análisis')
@section('page-subtitle', 'Historial completo de análisis de calidad LACTOMAT')

@section('top-actions')
    <a href="{{ route('quality.report-create') }}" class="btn btn-primary">
        <span>➕</span> Nuevo Análisis
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🔍 Filtros de Búsqueda</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('quality.reports') }}" class="form-grid">
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Resultado</label>
                <select name="result" class="form-select">
                    <option value="">Todos</option>
                    <option value="aprobado" {{ request('result') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option value="rechazado" {{ request('result') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                    <option value="aceptable" {{ request('result') === 'aceptable' ? 'selected' : '' }}>Aceptable</option>
                    <option value="observado" {{ request('result') === 'observado' ? 'selected' : '' }}>Observado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Productor</label>
                <select name="producer_id" class="form-select">
                    <option value="">Todos</option>
                    @forelse($producers ?? [] as $p)
                    <option value="{{ $p->id }}" {{ request('producer_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->user?->fullname ?? $p->code }}
                    </option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-end">
                <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                <a href="{{ route('quality.reports') }}" class="btn btn-ghost">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Lista de Reportes de Calidad</div>
        <div style="display:flex;gap:8px">
            <span class="badge badge-green">Aprobados: {{ $reports->where('result', 'aprobado')->count() ?? 0 }}</span>
            <span class="badge badge-red">Rechazados: {{ $reports->where('result', 'rechazado')->count() ?? 0 }}</span>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Fecha</th>
                <th>Entrega #</th>
                <th>Productor</th>
                <th>Resultado</th>
                <th>Puntuación</th>
                <th>Analista</th>
                <th>Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($reports ?? [] as $r)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $r->created_at?->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $r->created_at?->format('H:i') }}</div>
                    </td>
                    <td style="font-weight:600;color:#2563eb">#{{ $r->milk_delivery_id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $r->milkDelivery?->producer?->user?->fullname ?? 'N/A' }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $r->milkDelivery?->producer?->code ?? '' }}</div>
                    </td>
                    <td>
                        @if($r->result === 'aprobado') <span class="badge badge-green">✅ Aprobado</span>
                        @elseif($r->result === 'rechazado') <span class="badge badge-red">❌ Rechazado</span>
                        @elseif($r->result === 'aceptable') <span class="badge badge-blue">👍 Aceptable</span>
                        @else <span class="badge badge-amber">👁️ Observado</span> @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="font-weight:800;color:{{ ($r->qualityScore() ?? 0) >= 80 ? '#059669' : (($r->qualityScore() ?? 0) >= 60 ? '#d97706' : '#dc2626') }}">
                                {{ $r->qualityScore() ?? 0 }}%
                            </div>
                            <div style="flex:1;min-width:60px">
                                <div class="progress-wrap" style="height:6px">
                                    <div class="progress {{ ($r->qualityScore() ?? 0) >= 80 ? '' : (($r->qualityScore() ?? 0) >= 60 ? 'amber' : 'red') }}" style="width:{{ $r->qualityScore() ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $r->analyst?->fullname ?? 'Sistema' }}</td>
                    <td>
                        <a href="{{ route('quality.report-show', $r->id) }}" class="btn btn-sm btn-ghost">👁️ Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">📋</div><h3>Sin reportes de calidad</h3><p>No se encontraron análisis con los filtros seleccionados.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports ?? [], 'links'))
    <div style="padding:20px">
        {{ $reports->links() }}
    </div>
    @endif
</div>
@endsection
