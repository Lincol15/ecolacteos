@extends('layouts.app')

@section('page-title', 'Control de Calidad')
@section('page-subtitle', 'Análisis y reportes de calidad de la leche recibida')

@section('top-actions')
    <a href="{{ route('admin.quality') }}" class="btn btn-accent">
        📥 Exportar Reportes
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Reportes Totales</div>
        <div class="stat-value green">{{ $reports->count() }}</div>
        <div class="stat-icon-wrap green">🧪</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Puntaje Promedio</div>
        <div class="stat-value cyan">{{ $reports->count() ? number_format($reports->getCollection()->avg(fn($r) => $r->qualityScore()), 1) : 0 }}/100</div>
        <div class="stat-icon-wrap cyan">📊</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Con Observaciones</div>
        <div class="stat-value amber">{{ $reports->where('result', 'observado')->count() }}</div>
        <div class="stat-icon-wrap amber">⚠️</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Rechazados</div>
        <div class="stat-value red">{{ $reports->where('result', 'rechazado')->count() }}</div>
        <div class="stat-icon-wrap red">❌</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Reportes de Calidad</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.quality') }}" class="filters">
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
                <select name="result" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="aprobado" {{ request('result') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option value="observado" {{ request('result') == 'observado' ? 'selected' : '' }}>Observado</option>
                    <option value="rechazado" {{ request('result') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                    <option value="pendiente" {{ request('result') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Productor</label>
                <select name="producer_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @forelse($producers ?? [] as $prod)
                    <option value="{{ $prod->id }}" {{ request('producer_id') == $prod->id ? 'selected' : '' }}>
                        {{ $prod->code }} - {{ $prod->farm_name }}
                    </option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.quality') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha Análisis</th>
                        <th>Entrega #</th>
                        <th>Productor</th>
                        <th>Resultado</th>
                        <th>Puntaje</th>
                        <th>Analista</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    @php $rowProducer = $report->producer ?? $report->milkDelivery?->producer; @endphp
                    <tr>
                        <td>
                            <strong>{{ $report->analyzed_at ? $report->analyzed_at->format('d/m/Y') : '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">
                                {{ $report->analyzed_at ? $report->analyzed_at->format('H:i') : '' }}
                            </div>
                        </td>
                        <td><code class="badge badge-blue">{{ $report->milk_delivery_id ? '#' . $report->milk_delivery_id : 'Directo' }}</code></td>
                        <td>
                            <strong>{{ $rowProducer?->user?->fullname ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $rowProducer?->code ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($report->result) {
                                'aprobado', 'aceptable' => 'green',
                                'observado' => 'amber',
                                'rechazado' => 'red',
                                default => 'gray'
                            } }}">
                                {{ match($report->result) {
                                    'aprobado' => '✅ Aprobado',
                                    'aceptable' => '👍 Aceptable',
                                    'observado' => '⚠️ Observado',
                                    'rechazado' => '❌ Rechazado',
                                    default => '⏳ Pendiente'
                                } }}
                            </span>
                        </td>
                        <td>
                            @php
                                $s = $report->qualityScore();
                                $sColor = $s >= 85 ? 'green' : ($s >= 70 ? 'amber' : 'red');
                            @endphp
                            <span class="badge badge-{{ $sColor }}">
                                ⭐ {{ number_format($s, 1) }}/100
                            </span>
                            <div class="progress-wrap" style="margin-top:4px; width:100px;">
                                <div class="progress {{ $sColor }}" style="width:{{ min(100, $s) }}%"></div>
                            </div>
                        </td>
                        <td>{{ $report->analyst->name ?? '—' }} {{ $report->analyst->lastname ?? '' }}</td>
                        <td>
                            <a href="{{ route('quality.report-show', $report->id) }}" class="btn btn-info btn-sm">🔍 Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <div class="empty-icon">🧪</div>
                                <h3>No hay reportes de calidad</h3>
                                <p>Ajusta los filtros o espera que se generen nuevos análisis.</p>
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
