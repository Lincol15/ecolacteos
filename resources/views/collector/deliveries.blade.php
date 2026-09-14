@extends('layouts.app')

@section('title', 'Mis Entregas - VACA SYS')
@section('page-title', 'Mis Entregas de Leche')
@section('page-subtitle', 'Historial de entregas registradas como acopiador')

@section('top-actions')
    <a href="{{ route('collector.delivery-create') }}" class="btn btn-primary">
        <span>➕</span> Nueva Entrega
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🔍 Filtros de Búsqueda</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('collector.deliveries') }}" class="form-grid">
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
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="registrado" {{ request('status') === 'registrado' ? 'selected' : '' }}>⏳ Registrado</option>
                    <option value="aceptado" {{ request('status') === 'aceptado' ? 'selected' : '' }}>✅ Aceptado</option>
                    <option value="analizado" {{ request('status') === 'analizado' ? 'selected' : '' }}>🧪 Analizado</option>
                    <option value="rechazado" {{ request('status') === 'rechazado' ? 'selected' : '' }}>❌ Rechazado</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-end">
                <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                <a href="{{ route('collector.deliveries') }}" class="btn btn-ghost">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card green">
        <div class="stat-icon-wrap green">📊</div>
        <div class="stat-label">Total Litros</div>
        <div class="stat-value green">{{ number_format(($deliveries ?? collect())->sum('liters'), 2) }} L</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon-wrap amber">💲</div>
        <div class="stat-label">Monto Total</div>
        <div class="stat-value amber">S/ {{ number_format(($deliveries ?? collect())->sum(fn($d) => ($d->total_amount ?? ($d->liters * $d->price_per_liter))), 2) }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon-wrap blue">📋</div>
        <div class="stat-label">Total Entregas</div>
        <div class="stat-value blue">{{ count($deliveries ?? []) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Historial de Entregas</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Entrega #</th>
                <th>Fecha</th>
                <th>Productor</th>
                <th>Litros</th>
                <th>Temp.</th>
                <th>Precio/L</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Calidad</th>
            </tr></thead>
            <tbody>
                @forelse($deliveries ?? [] as $d)
                <tr>
                    <td style="font-weight:700;color:#2563eb">#{{ $d->id }}</td>
                    <td>
                        <div style="font-weight:600">{{ $d->delivery_date?->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $d->delivery_date?->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $d->producer?->user?->fullname ?? 'N/A' }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $d->producer?->code ?? '' }}</div>
                    </td>
                    <td style="font-weight:800;color:#059669">{{ number_format($d->liters, 2) }} L</td>
                    <td>{{ $d->temperature ?? '-' }}°C</td>
                    <td>S/ {{ number_format($d->price_per_liter, 2) }}</td>
                    <td style="font-weight:700;color:#d97706">S/ {{ number_format($d->total_amount ?? ($d->liters * $d->price_per_liter), 2) }}</td>
                    <td>
                        @if($d->status === 'aceptado' || $d->status === 'analizado') <span class="badge badge-green">✅ {{ ucfirst($d->status) }}</span>
                        @elseif($d->status === 'rechazado') <span class="badge badge-red">❌ {{ ucfirst($d->status) }}</span>
                        @else <span class="badge badge-amber">⏳ {{ ucfirst($d->status) }}</span> @endif
                    </td>
                    <td>
                        @if($d->qualityReport)
                        <span class="badge {{ $d->qualityReport->result === 'aprobado' ? 'badge-green' : ($d->qualityReport->result === 'rechazado' ? 'badge-red' : 'badge-amber') }}">
                            {{ $d->qualityReport->qualityScore() }}%
                        </span>
                        @else
                        <span class="badge badge-gray">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="empty"><div class="empty-icon">🚛</div><h3>Sin entregas registradas</h3><p>No hay entregas con los filtros seleccionados.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($deliveries ?? [], 'links'))
    <div style="padding:20px">
        {{ $deliveries->links() }}
    </div>
    @endif
</div>
@endsection
