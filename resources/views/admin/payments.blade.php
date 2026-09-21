@extends('layouts.app')

@section('page-title', 'Gestión de Pagos a Productores')
@section('page-subtitle', 'Liquidación y procesamiento de pagos por entrega de leche')

@section('top-actions')
    <a href="{{ route('admin.payments-process') }}" class="btn btn-accent">
        ⚙️ Procesar Pagos
    </a>
@endsection

@section('content')
@php($tab = request('tab', 'resumen'))
<div class="filters" style="margin-bottom:20px;">
    <a href="{{ route('admin.payments', array_merge(request()->except('tab'), ['tab' => 'resumen'])) }}" class="btn btn-sm {{ $tab === 'resumen' ? 'btn-primary' : 'btn-ghost' }}">📊 Resumen Litros</a>
    <a href="{{ route('admin.payments', array_merge(request()->except('tab'), ['tab' => 'generados'])) }}" class="btn btn-sm {{ $tab === 'generados' ? 'btn-primary' : 'btn-ghost' }}">💳 Pagos Generados</a>
    <a href="{{ route('admin.payments', array_merge(request()->except('tab'), ['tab' => 'acopiador'])) }}" class="btn btn-sm {{ $tab === 'acopiador' ? 'btn-primary' : 'btn-ghost' }}">🚜 Resumen por Acopiador</a>
</div>

@if($tab === 'resumen')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Pagos Pagados</div>
        <div class="stat-value green">{{ $payments->where('status', 'pagado')->count() }}</div>
        <div class="stat-icon-wrap green">✅</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pagos Pendientes</div>
        <div class="stat-value amber">{{ $payments->where('status', 'pendiente')->count() }}</div>
        <div class="stat-icon-wrap amber">⏳</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Litros Liquidados</div>
        <div class="stat-value blue">{{ number_format($payments->sum('total_liters'), 0) }} L</div>
        <div class="stat-icon-wrap blue">🥛</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Monto Total Período</div>
        <div class="stat-value purple">S/{{ number_format($payments->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap purple">💵</div>
    </div>
</div>
@endif

@if($tab === 'generados')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">💳 Liquidaciones de Pago</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.payments') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Período</label>
                <input type="month" name="period" value="{{ request('period') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="procesando" {{ request('status') == 'procesando' ? 'selected' : '' }}>Procesando</option>
                    <option value="parcial" {{ request('status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                    <option value="pagado" {{ request('status') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="rechazado" {{ request('status') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Método Pago</label>
                <select name="method" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="transferencia" {{ request('method') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="efectivo" {{ request('method') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="cheque" {{ request('method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('admin.payments') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Productor</th>
                        <th>Litros</th>
                        <th>Precio/L</th>
                        <th>Base</th>
                        <th>Bonos</th>
                        <th>Deducc. / Llevado a Planta</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td>
                            <code class="badge badge-blue">{{ $pay->period_code ?? '—' }}</code>
                            <div style="font-size:11px; color:var(--text-light);">{{ $pay->getPeriodLabelAttribute() }}</div>
                        </td>
                        <td>
                            <strong>{{ $pay->producer?->user?->fullname ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $pay->producer->code ?? '' }}</div>
                        </td>
                        <td><strong>{{ number_format($pay->total_liters, 0) }} L</strong></td>
                        <td>S/{{ number_format($pay->precio_por_litro ?? $pay->avg_price_per_liter ?? 0, 3) }}</td>
                        <td>S/{{ number_format($pay->base_amount ?? 0, 2) }}</td>
                        <td style="color:#059669;">+S/{{ number_format(($pay->quality_bonus ?? 0) + ($pay->production_bonus ?? 0), 2) }}</td>
                        <td style="color:#dc2626;">-S/{{ number_format(($pay->deductions ?? 0) + ($pay->llevado_a_planta ?? 0), 2) }}</td>
                        <td><strong style="font-size:15px;">S/{{ number_format($pay->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($pay->status) {
                                'pendiente' => 'red',
                                'procesando' => 'amber',
                                'parcial' => 'amber',
                                'pagado' => 'green',
                                'rechazado' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($pay->status) {
                                    'pendiente' => '❌ Pendiente',
                                    'procesando' => '⏳ Procesando',
                                    'parcial' => '🟡 Parcial',
                                    'pagado' => '✅ Pagado',
                                    'rechazado' => '🚫 Rechazado',
                                    default => ucfirst($pay->status)
                                } }}
                            </span>
                        </td>
                        <td>{{ match($pay->payment_method) {
                            'transferencia' => '🏦 Transferencia',
                            'efectivo' => '💵 Efectivo',
                            'cheque' => '📄 Cheque',
                            default => $pay->payment_method ?? '—'
                        } }}</td>
                        <td>
                            @if($pay->status !== 'pagado')
                            <form method="POST" action="{{ route('admin.payment-mark-paid', $pay) }}" onsubmit="return confirm('¿Marcar este pago como pagado?')">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-sm btn-primary">✅ Marcar Pagado</button>
                            </form>
                            @else
                            <span class="badge badge-gray">Sin acciones</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty">
                                <div class="empty-icon">💳</div>
                                <h3>No hay pagos registrados</h3>
                                <p>Presiona "Procesar Pagos" para generar la liquidación del período actual.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($tab === 'acopiador')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚜 Resumen de Litros y Monto por Acopiador</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Acopiador</th><th>Entregas</th><th>Litros</th><th>Monto Total</th></tr></thead>
            <tbody>
                @forelse($collectorSummary as $row)
                <tr>
                    <td><strong>{{ $row->collector?->fullname ?? 'Sin acopiador' }}</strong></td>
                    <td>{{ $row->deliveries }}</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($row->liters, 2) }} L</td>
                    <td style="font-weight:700;color:#d97706">S/ {{ number_format($row->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty"><div class="empty-icon">🚜</div><h3>Sin datos para el período seleccionado</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
