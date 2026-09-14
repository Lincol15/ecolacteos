@extends('layouts.app')

@section('page-title', 'Gestión de Pagos a Productores')
@section('page-subtitle', 'Liquidación y procesamiento de pagos por entrega de leche')

@section('top-actions')
    <form method="POST" action="{{ route('admin.payments-process') }}" onsubmit="return confirm('¿Confirmar procesamiento de pagos para el período actual?')">
        @csrf
        <button type="submit" class="btn btn-accent">
            ⚙️ Procesar Pagos
        </button>
    </form>
@endsection

@section('content')
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
                    <option value="pagado" {{ request('status') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="anulado" {{ request('status') == 'anulado' ? 'selected' : '' }}>Anulado</option>
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
                        <th>Precio Prom.</th>
                        <th>Base</th>
                        <th>Bonos</th>
                        <th>Deducciones</th>
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
                            <code class="badge badge-blue">{{ $pay->period ?? '—' }}</code>
                        </td>
                        <td>
                            <strong>{{ $pay->producer->farm_name ?? '—' }}</strong>
                            <div style="font-size:11px; color:var(--text-light);">{{ $pay->producer->code ?? '' }}</div>
                        </td>
                        <td><strong>{{ number_format($pay->total_liters, 0) }} L</strong></td>
                        <td>S/{{ number_format($pay->avg_price_per_liter ?? 0, 3) }}</td>
                        <td>S/{{ number_format($pay->base_amount ?? 0, 2) }}</td>
                        <td style="color:#059669;">+S/{{ number_format($pay->bonuses_amount ?? 0, 2) }}</td>
                        <td style="color:#dc2626;">-S/{{ number_format($pay->deductions_amount ?? 0, 2) }}</td>
                        <td><strong style="font-size:15px;">S/{{ number_format($pay->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($pay->status) {
                                'pendiente' => 'red',
                                'procesando' => 'amber',
                                'pagado' => 'green',
                                'anulado' => 'gray',
                                default => 'gray'
                            } }}">
                                {{ match($pay->status) {
                                    'pendiente' => '❌ Pendiente',
                                    'procesando' => '⏳ Procesando',
                                    'pagado' => '✅ Pagado',
                                    'anulado' => '🚫 Anulado',
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
                            <button class="btn btn-info btn-sm">🔍 Detalle</button>
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
@endsection
