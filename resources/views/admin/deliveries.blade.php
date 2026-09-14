@extends('layouts.app')

@section('page-title', 'Entregas de Leche')
@section('page-subtitle', 'Registro y control de entregas de leche de productores')

@section('top-actions')
    <a href="{{ route('admin.deliveries') }}" class="btn btn-accent">
        📥 Exportar Excel
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Entregas</div>
        <div class="stat-value green">{{ $deliveries->count() }}</div>
        <div class="stat-icon-wrap green">🚛</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Litros Recibidos (Período)</div>
        <div class="stat-value amber">{{ number_format($deliveries->sum('liters'), 0) }} L</div>
        <div class="stat-icon-wrap amber">🥛</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Monto Total</div>
        <div class="stat-value blue">S/{{ number_format($deliveries->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap blue">💵</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Temp. Promedio</div>
        <div class="stat-value cyan">{{ $deliveries->count() ? number_format($deliveries->avg('temperature'), 1) : 0 }}°C</div>
        <div class="stat-icon-wrap cyan">🌡️</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📦 Registro de Entregas</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.deliveries') }}" class="filters">
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
                    <a href="{{ route('admin.deliveries') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Productor</th>
                        <th>Litros</th>
                        <th>Temp.</th>
                        <th>Precio/L</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Calidad</th>
                        <th>Acopiador</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td>
                            <strong>{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('d/m/Y') : '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">
                                {{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('H:i') : '' }}
                            </div>
                        </td>
                        <td>
                            <strong>{{ $delivery->producer->farm_name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $delivery->producer->code ?? '' }}</div>
                        </td>
                        <td><strong>{{ number_format($delivery->liters, 1) }} L</strong></td>
                        <td>
                            <span class="badge {{ ($delivery->temperature ?? 0) > 8 ? 'badge-amber' : 'badge-green' }}">
                                {{ number_format($delivery->temperature, 1) }}°C
                            </span>
                        </td>
                        <td>S/{{ number_format($delivery->price_per_liter, 3) }}</td>
                        <td><strong>S/{{ number_format($delivery->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($delivery->status) {
                                'recibido' => 'blue',
                                'procesando' => 'amber',
                                'aceptado' => 'green',
                                'rechazado' => 'red',
                                default => 'gray'
                            } }}">
                                {{ match($delivery->status) {
                                    'recibido' => '📥 Recibido',
                                    'procesando' => '⏳ Procesando',
                                    'aceptado' => '✅ Aceptado',
                                    'rechazado' => '❌ Rechazado',
                                    default => ucfirst($delivery->status)
                                } }}
                            </span>
                        </td>
                        <td>
                            @if(isset($delivery->qualityReport))
                                <span class="badge badge-{{ $delivery->qualityReport->result == 'aprobado' ? 'green' : ($delivery->qualityReport->result == 'rechazado' ? 'red' : 'amber') }}">
                                    {{ $delivery->qualityReport->result == 'aprobado' ? '✅ Aprob' : ($delivery->qualityReport->result == 'rechazado' ? '❌ Rech' : '⚠️ Obs') }} {{ $delivery->qualityReport->score }}/100
                                </span>
                            @else
                                <span class="badge badge-gray">⏳ Pendiente</span>
                            @endif
                        </td>
                        <td>{{ $delivery->collector->name ?? '—' }} {{ $delivery->collector->lastname ?? '' }}</td>
                        <td>
                            <button class="btn btn-info btn-sm">🔍 Detalle</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty">
                                <div class="empty-icon">🚛</div>
                                <h3>No hay entregas registradas</h3>
                                <p>Prueba ajustando los filtros de fecha o estado.</p>
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
