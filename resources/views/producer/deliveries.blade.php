@extends('layouts.app')

@section('page-title', 'Mis Entregas de Leche')
@section('page-subtitle', 'Seguimiento de tus entregas a la planta')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Entregas</div>
        <div class="stat-value green">{{ $deliveries->count() }}</div>
        <div class="stat-icon-wrap green">🚛</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Litros Entregados</div>
        <div class="stat-value amber">{{ number_format($deliveries->sum('liters'), 0) }} L</div>
        <div class="stat-icon-wrap amber">🥛</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Monto Total</div>
        <div class="stat-value blue">S/{{ number_format($deliveries->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap blue">💵</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Promedio Temp.</div>
        <div class="stat-value cyan">{{ $deliveries->count() ? number_format($deliveries->avg('temperature'), 1) : 0 }}°C</div>
        <div class="stat-icon-wrap cyan">🌡️</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Historial de Entregas</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('producer.deliveries') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="recibido" {{ request('status') == 'recibido' ? 'selected' : '' }}>Recibido</option>
                    <option value="procesando" {{ request('status') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                    <option value="aceptado" {{ request('status') == 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                    <option value="rechazado" {{ request('status') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('producer.deliveries') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Litros</th>
                        <th>Temp.</th>
                        <th>Precio/L</th>
                        <th>Subtotal</th>
                        <th>Estado</th>
                        <th>Calidad</th>
                        <th>Acopiador</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $d)
                    <tr>
                        <td>
                            <strong>{{ $d->delivery_date ? \Carbon\Carbon::parse($d->delivery_date)->format('d/m/Y') : '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $d->delivery_date ? \Carbon\Carbon::parse($d->delivery_date)->format('H:i') : '' }}</div>
                        </td>
                        <td><strong>{{ number_format($d->liters, 1) }} L</strong></td>
                        <td>
                            <span class="badge {{ ($d->temperature ?? 0) > 8 ? 'badge-amber' : 'badge-green' }}">
                                {{ number_format($d->temperature, 1) }}°C
                            </span>
                        </td>
                        <td>S/{{ number_format($d->price_per_liter ?? 0, 3) }}</td>
                        <td><strong>S/{{ number_format($d->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($d->status) {
                                'recibido' => 'blue', 'procesando' => 'amber', 'aceptado' => 'green', 'rechazado' => 'red', default => 'gray'
                            } }}">
                                {{ match($d->status) {
                                    'recibido' => '📥 Recibido',
                                    'procesando' => '⏳ Procesando',
                                    'aceptado' => '✅ Aceptado',
                                    'rechazado' => '❌ Rechazado',
                                    default => ucfirst($d->status)
                                } }}
                            </span>
                        </td>
                        <td>
                            @if(isset($d->qualityReport))
                                <span class="badge badge-{{ $d->qualityReport->result == 'aprobado' ? 'green' : ($d->qualityReport->result == 'rechazado' ? 'red' : 'amber') }}">
                                    {{ $d->qualityReport->result == 'aprobado' ? '✅' : ($d->qualityReport->result == 'rechazado' ? '❌' : '⚠️') }} {{ number_format($d->qualityReport->score, 0) }}/100
                                </span>
                            @else
                                <span class="badge badge-gray">⏳ Pendiente</span>
                            @endif
                        </td>
                        <td>{{ $d->collector->name ?? '—' }} {{ $d->collector->lastname ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">🚛</div>
                                <h3>Aún no tienes entregas registradas</h3>
                                <p>Tu historial de entregas aparecerá aquí.</p>
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
