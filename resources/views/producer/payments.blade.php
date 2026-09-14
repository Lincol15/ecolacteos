@extends('layouts.app')

@section('page-title', 'Mis Pagos / Liquidaciones')
@section('page-subtitle', 'Historial de pagos y liquidaciones por leche entregada')

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
        <div class="stat-label">Monto Recibido</div>
        <div class="stat-value purple">S/{{ number_format($payments->where('status', 'pagado')->sum('total_amount'), 2) }}</div>
        <div class="stat-icon-wrap purple">💵</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">💳 Historial de Liquidaciones</div>
    </div>
    <div class="panel-body">
        <form method="GET" action="{{ route('producer.payments') }}" class="filters">
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
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="{{ route('producer.payments') }}" class="btn btn-ghost btn-sm">✖ Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
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
                    @forelse($payments as $p)
                    <tr>
                        <td><code class="badge badge-blue">{{ $p->period ?? '—' }}</code></td>
                        <td><strong>{{ number_format($p->total_liters, 0) }} L</strong></td>
                        <td>S/{{ number_format($p->avg_price_per_liter ?? 0, 3) }}</td>
                        <td>S/{{ number_format($p->base_amount ?? 0, 2) }}</td>
                        <td style="color:#059669;">+S/{{ number_format($p->bonuses_amount ?? 0, 2) }}</td>
                        <td style="color:#dc2626;">-S/{{ number_format($p->deductions_amount ?? 0, 2) }}</td>
                        <td><strong style="font-size:15px;">S/{{ number_format($p->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ match($p->status) {
                                'pendiente' => 'red', 'procesando' => 'amber', 'pagado' => 'green', default => 'gray'
                            } }}">
                                {{ match($p->status) {
                                    'pendiente' => '❌ Pendiente',
                                    'procesando' => '⏳ Procesando',
                                    'pagado' => '✅ Pagado',
                                    default => ucfirst($p->status)
                                } }}
                            </span>
                        </td>
                        <td>{{ match($p->payment_method) {
                            'transferencia' => '🏦 Transfer.',
                            'efectivo' => '💵 Efectivo',
                            'cheque' => '📄 Cheque',
                            default => $p->payment_method ?? '—'
                        } }}</td>
                        <td>
                            <a href="{{ route('producer.payment-show', $p) }}" class="btn btn-info btn-sm">🔍 Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty">
                                <div class="empty-icon">💳</div>
                                <h3>Aún no tienes liquidaciones</h3>
                                <p>Los pagos procesados aparecerán aquí.</p>
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
