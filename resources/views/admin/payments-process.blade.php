@extends('layouts.app')

@section('page-title', 'Procesar Pagos')
@section('page-subtitle', 'Genera liquidaciones semanales para productores con entregas pendientes de pago')

@section('top-actions')
    <a href="{{ route('admin.payments') }}" class="btn btn-ghost">← Volver a Pagos</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📅 Seleccionar Período</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.payments-process') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="period_start" value="{{ request('period_start', $start->toDateString()) }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="period_end" value="{{ request('period_end', $end->toDateString()) }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-sm">🔍 Buscar Entregas</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">💳 Productores con Liquidación Pendiente ({{ $previews->count() }})</div>
    </div>
    <div class="panel-body">
        @if($previews->isEmpty())
        <div class="empty">
            <div class="empty-icon">✅</div>
            <h3>No hay liquidaciones pendientes</h3>
            <p>Todos los productores con entregas en este período ya tienen un pago generado, o no hubo entregas.</p>
        </div>
        @else
        <form method="POST" action="{{ route('admin.payments-generate') }}">
            @csrf
            <input type="hidden" name="period_start" value="{{ $start->toDateString() }}">
            <input type="hidden" name="period_end" value="{{ $end->toDateString() }}">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="document.querySelectorAll('.producer-check').forEach(c => c.checked = this.checked)" checked></th>
                            <th>Productor</th>
                            <th>Litros Totales</th>
                            <th>Precio/L</th>
                            <th>Base</th>
                            <th>Bonos</th>
                            <th>Deducc. / Llevado a Planta</th>
                            <th>Total a Pagar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previews as $preview)
                        <tr>
                            <td><input type="checkbox" class="producer-check" name="producer_ids[]" value="{{ $preview['producer_id'] }}" checked></td>
                            <td>
                                @php $producer = \App\Models\Producer::with('user')->find($preview['producer_id']); @endphp
                                <strong>{{ $producer?->user?->fullname ?? 'N/A' }}</strong>
                                <div style="font-size:11px;color:var(--text-light)">{{ $producer?->code }}</div>
                            </td>
                            <td><strong>{{ number_format($preview['total_liters'], 1) }} L</strong></td>
                            <td>S/{{ number_format($preview['precio_por_litro'], 3) }}</td>
                            <td>S/{{ number_format($preview['base_amount'], 2) }}</td>
                            <td style="color:#059669">+S/{{ number_format($preview['quality_bonus'] + $preview['production_bonus'], 2) }}</td>
                            <td style="color:#dc2626">-S/{{ number_format($preview['deductions'] + $preview['llevado_a_planta'], 2) }}</td>
                            <td><strong style="font-size:15px">S/{{ number_format($preview['total_amount'], 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" onclick="return confirm('¿Generar las liquidaciones seleccionadas?')">💾 Generar Liquidaciones</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
