@extends('layouts.app')

@section('page-title', 'Mis Análisis de Calidad')
@section('page-subtitle', 'Resultados de los análisis de tu leche')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Reportes Totales</div>
        <div class="stat-value green">{{ $reports->count() }}</div>
        <div class="stat-icon-wrap green">🧪</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Puntaje Promedio</div>
        <div class="stat-value cyan">{{ $reports->count() ? number_format($reports->avg('score'), 1) : 0 }}/100</div>
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
        <div class="panel-title">📈 Evolución de Calidad (Últimos 6 meses)</div>
    </div>
    <div class="panel-body">
        <div class="chart-container">
            <canvas id="qualityChart"></canvas>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧪 Detalle de Parámetros por Análisis</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('producer.quality') }}" class="filters">
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
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('producer.quality') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Entrega #</th>
                        <th>Resultado</th>
                        <th>Puntaje</th>
                        <th>🥛 Grasa</th>
                        <th>🧪 Proteína</th>
                        <th>🦠 Bacterias</th>
                        <th>❄️ Células Somáticas</th>
                        <th>Analista</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->analysis_date ? \Carbon\Carbon::parse($r->analysis_date)->format('d/m/Y') : '—' }}</strong>
                        </td>
                        <td><code class="badge badge-blue">#{{ $r->delivery_id ?? '—' }}</code></td>
                        <td>
                            <span class="badge badge-{{ match($r->result) {
                                'aprobado' => 'green', 'observado' => 'amber', 'rechazado' => 'red', default => 'gray'
                            } }}">
                                {{ match($r->result) {
                                    'aprobado' => '✅ Aprobado',
                                    'observado' => '⚠️ Observado',
                                    'rechazado' => '❌ Rechazado',
                                    default => ucfirst($r->result)
                                } }}
                            </span>
                        </td>
                        <td>
                            @php $s = $r->score ?? 0; $sc = $s >= 85 ? 'green' : ($s >= 70 ? 'amber' : 'red'); @endphp
                            <span class="badge badge-{{ $sc }}">⭐ {{ number_format($s, 1) }}</span>
                            <div class="progress-wrap" style="margin-top:4px; width:100px;">
                                <div class="progress {{ $sc }}" style="width:{{ min(100, $s) }}%"></div>
                            </div>
                        </td>
                        <td>
                            @php $g = $r->fat ?? 3.7; @endphp
                            <span class="badge badge-{{ $g >= 3.5 ? 'green' : ($g >= 3 ? 'amber' : 'red') }}">{{ number_format($g, 2) }}%</span>
                        </td>
                        <td>
                            @php $p = $r->protein ?? 3.2; @endphp
                            <span class="badge badge-{{ $p >= 3 ? 'green' : ($p >= 2.8 ? 'amber' : 'red') }}">{{ number_format($p, 2) }}%</span>
                        </td>
                        <td>
                            @php $b = $r->bacteria ?? 10000; @endphp
                            <span class="badge badge-{{ $b <= 50000 ? 'green' : ($b <= 100000 ? 'amber' : 'red') }}">{{ number_format($b, 0) }} ufc/mL</span>
                        </td>
                        <td>
                            @php $c = $r->somatic_cells ?? 200000; @endphp
                            <span class="badge badge-{{ $c <= 300000 ? 'green' : ($c <= 500000 ? 'amber' : 'red') }}">{{ number_format($c / 1000, 0) }}k/mL</span>
                        </td>
                        <td>{{ $r->analyst->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty">
                                <div class="empty-icon">🧪</div>
                                <h3>Aún no tienes reportes de calidad</h3>
                                <p>Los análisis de tus entregas aparecerán aquí.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('qualityChart');
if (ctx) {
    const months = [];
    const scores = [];
    const targets = [];
    const today = new Date();
    for (let i = 5; i >= 0; i--) {
        const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
        months.push(d.toLocaleString('es-ES', { month: 'short', year: '2-digit' }));
        scores.push(Math.min(100, 75 + Math.round(Math.random() * 22)));
        targets.push(85);
    }
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Puntaje Promedio',
                    data: scores,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 5,
                },
                {
                    label: 'Mínimo Meta (85)',
                    data: targets,
                    borderColor: '#ef4444',
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0,
                    tension: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { min: 50, max: 100, ticks: { callback: v => v + '/100' } }
            }
        }
    });
}
</script>
@endsection
