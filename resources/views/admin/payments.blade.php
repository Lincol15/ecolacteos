@extends('layouts.app')

@section('page-title', 'Centro de Pagos')
@section('page-subtitle', 'Liquidaciones a productores por litro y planilla mensual de acopiadores')

@php($canManage = auth()->user()->isAdmin())

@section('top-actions')
    @if($canManage && $tab === 'productores')
        <a href="{{ route('admin.payments-process') }}" class="btn btn-primary">
            <x-icon name="receipt" style="width:18px;height:18px" /> Generar liquidaciones
        </a>
    @endif
@endsection

@section('content')
<div class="pay-kpis">
    <div class="pay-kpi">
        <div class="pay-kpi-icon amber"><x-icon name="clock" /></div>
        <div>
            <div class="pay-kpi-label">Por pagar a productores</div>
            <div class="pay-kpi-value">S/ {{ number_format($kpis['pending_producers'], 2) }}</div>
            <div class="pay-kpi-hint">Liquidaciones pendientes</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon purple"><x-icon name="truck" /></div>
        <div>
            <div class="pay-kpi-label">Por pagar a acopiadores</div>
            <div class="pay-kpi-value">S/ {{ number_format($kpis['pending_collectors'], 2) }}</div>
            <div class="pay-kpi-hint">Sueldos pendientes</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon green"><x-icon name="check" /></div>
        <div>
            <div class="pay-kpi-label">Pagado este mes</div>
            <div class="pay-kpi-value">S/ {{ number_format($kpis['paid_this_month'], 2) }}</div>
            <div class="pay-kpi-hint">{{ ucfirst(now()->locale('es')->translatedFormat('F Y')) }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon blue"><x-icon name="milk" /></div>
        <div>
            <div class="pay-kpi-label">Litros liquidados del mes</div>
            <div class="pay-kpi-value">{{ number_format($kpis['liters_this_month'], 0) }} L</div>
            <div class="pay-kpi-hint">Pagados por litro a productores</div>
        </div>
    </div>
</div>

<nav class="pay-tabs" aria-label="Tipo de pago">
    <a href="{{ route('admin.payments', ['tab' => 'productores']) }}" class="pay-tab {{ $tab === 'productores' ? 'active' : '' }}">
        <x-icon name="sprout" /> Productores <small>· por litro</small>
    </a>
    <a href="{{ route('admin.payments', ['tab' => 'acopiadores']) }}" class="pay-tab {{ $tab === 'acopiadores' ? 'active' : '' }}">
        <x-icon name="truck" /> Acopiadores <small>· sueldo mensual</small>
    </a>
</nav>

@if($tab === 'productores')
<div class="panel">
    <form method="GET" action="{{ route('admin.payments') }}" class="pay-toolbar">
        <input type="hidden" name="tab" value="productores">
        <div class="form-group grow">
            <label class="form-label">Buscar productor</label>
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nombre o código...">
        </div>
        <div class="form-group">
            <label class="form-label">Período</label>
            <input type="month" name="period" value="{{ request('period') }}" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach(\App\Models\Payment::STATUS as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Método</label>
            <select name="method" class="form-select">
                <option value="">Todos</option>
                @foreach(\App\Models\Payment::PAYMENT_METHODS as $value => $label)
                <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="{{ route('admin.payments') }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr>
                    <th>Productor</th>
                    <th>Período</th>
                    <th>Litros × Precio</th>
                    <th>Bonos</th>
                    <th>Descuentos</th>
                    <th>Total neto</th>
                    <th>Estado</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td>
                        <div class="pay-person">
                            <span class="pay-avatar">{{ strtoupper(substr($pay->producer?->user?->name ?? 'P', 0, 1).substr($pay->producer?->user?->lastname ?? '', 0, 1)) }}</span>
                            <div>
                                <strong>{{ $pay->producer?->user?->fullname ?? '—' }}</strong>
                                <small>{{ $pay->producer?->code }} · {{ $pay->receipt_number }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $pay->period_start?->format('d/m') }} – {{ $pay->period_end?->format('d/m/Y') }}</div>
                        <div class="pay-muted">{{ $pay->period_code }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ number_format($pay->total_liters, 1) }} L</div>
                        <div class="pay-muted">× S/ {{ number_format($pay->precio_por_litro ?? $pay->avg_price_per_liter, 2) }}</div>
                    </td>
                    <td class="pay-plus">+ S/ {{ number_format($pay->bonus_total, 2) }}</td>
                    <td class="pay-minus">− S/ {{ number_format($pay->discount_total, 2) }}</td>
                    <td class="pay-amount">S/ {{ number_format($pay->total_amount, 2) }}</td>
                    <td>
                        <x-payment-status :status="$pay->status" />
                        @if($pay->status === 'pagado' && $pay->payment_date)
                        <div class="pay-muted" style="margin-top:4px">{{ $pay->payment_date->format('d/m/Y') }} · {{ \App\Models\Payment::PAYMENT_METHODS[$pay->payment_method] ?? '' }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="pay-actions">
                            <a href="{{ route('admin.payment-receipt', $pay) }}" class="btn-icon" title="Ver comprobante">
                                <x-icon name="receipt" /> Comprobante
                            </a>
                            @if($canManage && $pay->status !== 'pagado')
                            <button type="button" class="btn-icon primary" data-pay-open
                                    data-action="{{ route('admin.payment-mark-paid', $pay) }}"
                                    data-name="{{ $pay->producer?->user?->fullname }}"
                                    data-amount="S/ {{ number_format($pay->total_amount, 2) }}">
                                <x-icon name="check" /> Pagar
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty">
                            <div class="empty-icon">💳</div>
                            <h3>No hay liquidaciones</h3>
                            <p>Usa "Generar liquidaciones" para calcular los pagos por litro de un período.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div style="padding:16px 20px">{{ $payments->links() }}</div>
    @endif
</div>
@endif

@if($tab === 'acopiadores')
<div class="panel">
    <div class="panel-header" style="flex-wrap:wrap; gap:12px;">
        <div>
            <div class="panel-title">Planilla de {{ ucfirst($payrollMonth->locale('es')->translatedFormat('F Y')) }}</div>
            <div class="pay-muted" style="margin-top:4px">
                Total S/ {{ number_format($payroll['total'], 2) }} ·
                Pagado S/ {{ number_format($payroll['paid'], 2) }} ·
                Pendiente S/ {{ number_format($payroll['pending'], 2) }}
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <form method="GET" action="{{ route('admin.payments') }}" style="display:flex; gap:8px;">
                <input type="hidden" name="tab" value="acopiadores">
                <input type="month" name="month" value="{{ $payrollMonth->format('Y-m') }}" class="form-input" onchange="this.form.submit()" aria-label="Mes de la planilla">
            </form>
            @if($canManage && $payroll['missing'] > 0)
            <form method="POST" action="{{ route('admin.collector-payments-generate') }}" onsubmit="return confirm('¿Generar los sueldos de {{ $payrollMonth->locale('es')->translatedFormat('F Y') }}?')">
                @csrf
                <input type="hidden" name="month" value="{{ $payrollMonth->format('Y-m') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <x-icon name="calendar" style="width:16px;height:16px" /> Generar planilla ({{ $payroll['missing'] }})
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr>
                    <th>Acopiador</th>
                    <th>Sueldo mensual</th>
                    <th>Acopio del mes</th>
                    <th>Pago del mes</th>
                    <th>Estado</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collectors as $collector)
                @php($monthPayment = $collector->collectorPayments->first())
                <tr>
                    <td>
                        <div class="pay-person">
                            <span class="pay-avatar">{{ strtoupper(substr($collector->name, 0, 1).substr($collector->lastname ?? '', 0, 1)) }}</span>
                            <div>
                                <strong>{{ $collector->fullname }}</strong>
                                <small>{{ $collector->comunidad ?? 'Sin comunidad' }}@unless($collector->active) · Inactivo @endunless</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($canManage)
                        <form method="POST" action="{{ route('admin.collector-salary-update', $collector) }}" class="salary-form">
                            @csrf @method('PUT')
                            <span class="pay-muted">S/</span>
                            <input type="number" name="monthly_salary" step="0.01" min="0" class="form-input"
                                   value="{{ $collector->monthly_salary }}" placeholder="0.00" aria-label="Sueldo mensual de {{ $collector->fullname }}">
                            <button type="submit" class="btn-icon" title="Guardar sueldo"><x-icon name="check" /></button>
                        </form>
                        @else
                        <span class="pay-amount">{{ $collector->monthly_salary ? 'S/ '.number_format($collector->monthly_salary, 2) : '—' }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600">{{ number_format($collector->month_liters ?? 0, 1) }} L</div>
                        <div class="pay-muted">Informativo</div>
                    </td>
                    <td class="pay-amount">{{ $monthPayment ? 'S/ '.number_format($monthPayment->total_amount, 2) : '—' }}</td>
                    <td>
                        @if($monthPayment)
                            <x-payment-status :status="$monthPayment->status" />
                        @else
                            <span class="status-pill status-rejected">Sin generar</span>
                        @endif
                    </td>
                    <td>
                        <div class="pay-actions">
                            @if($monthPayment)
                            <a href="{{ route('admin.collector-payment-receipt', $monthPayment) }}" class="btn-icon"><x-icon name="receipt" /> Boleta</a>
                            @if($canManage && $monthPayment->status !== 'pagado')
                            <button type="button" class="btn-icon primary" data-pay-open
                                    data-action="{{ route('admin.collector-payment-mark-paid', $monthPayment) }}"
                                    data-name="{{ $collector->fullname }}"
                                    data-amount="S/ {{ number_format($monthPayment->total_amount, 2) }}">
                                <x-icon name="check" /> Pagar
                            </button>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <div class="empty-icon">🚜</div>
                            <h3>No hay acopiadores</h3>
                            <p>Crea usuarios con rol "Acopiador" desde Usuarios.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($canManage)
    <div class="pay-muted" style="padding:14px 20px; border-top:1px solid var(--border);">
        Define el sueldo de cada acopiador y luego genera la planilla del mes. Los acopiadores reciben un sueldo fijo mensual; los litros son solo referencia.
    </div>
    @endif
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Historial de sueldos pagados</div>
    </div>
    <div class="table-wrap">
        <table class="pay-table">
            <thead><tr><th>Boleta</th><th>Acopiador</th><th>Mes</th><th>Neto</th><th>Estado</th><th style="text-align:right"></th></tr></thead>
            <tbody>
                @forelse($collectorHistory as $cp)
                <tr>
                    <td><code class="badge badge-gray">{{ $cp->receipt_number }}</code></td>
                    <td><strong>{{ $cp->collector?->fullname ?? '—' }}</strong></td>
                    <td>{{ $cp->period_label }}</td>
                    <td class="pay-amount">S/ {{ number_format($cp->total_amount, 2) }}</td>
                    <td><x-payment-status :status="$cp->status" /></td>
                    <td><div class="pay-actions"><a href="{{ route('admin.collector-payment-receipt', $cp) }}" class="btn-icon"><x-icon name="receipt" /> Boleta</a></div></td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><h3>Aún no hay planillas generadas</h3></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($collectorHistory->hasPages())
    <div style="padding:16px 20px">{{ $collectorHistory->links() }}</div>
    @endif
</div>
@endif

@if($canManage)
<dialog class="pay-dialog" id="payDialog">
    <form method="POST" id="payDialogForm">
        @csrf @method('PUT')
        <div class="pay-dialog-head">
            <h3>Registrar pago</h3>
            <p><span id="payDialogName"></span> · <strong id="payDialogAmount"></strong></p>
        </div>
        <div class="pay-dialog-body">
            <div class="form-group" style="margin:0">
                <label class="form-label" for="payMethod">Método de pago</label>
                <select name="payment_method" id="payMethod" class="form-select">
                    @foreach(\App\Models\Payment::PAYMENT_METHODS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label" for="payTxn">N° de operación (opcional)</label>
                <input type="text" name="transaction_number" id="payTxn" maxlength="50" class="form-input" placeholder="Se genera uno si lo dejas vacío">
            </div>
        </div>
        <div class="pay-dialog-foot">
            <button type="button" class="btn btn-ghost btn-sm" data-pay-close>Cancelar</button>
            <button type="submit" class="btn btn-primary btn-sm">Confirmar pago</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    const dialog = document.getElementById('payDialog');
    const form = document.getElementById('payDialogForm');
    document.querySelectorAll('[data-pay-open]').forEach((button) => {
        button.addEventListener('click', () => {
            form.action = button.dataset.action;
            document.getElementById('payDialogName').textContent = button.dataset.name;
            document.getElementById('payDialogAmount').textContent = button.dataset.amount;
            document.getElementById('payTxn').value = '';
            dialog.showModal();
        });
    });
    dialog.querySelector('[data-pay-close]').addEventListener('click', () => dialog.close());
})();
</script>
@endif
@endsection
