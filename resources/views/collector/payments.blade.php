@extends('layouts.app')

@section('page-title', 'Mis Pagos')
@section('page-subtitle', 'Tu sueldo mensual como acopiador')

@section('content')
@if($latestPayment)
<section class="pay-hero">
    <div>
        <div class="pay-hero-label">Sueldo de {{ $latestPayment->period_label }}</div>
        <div class="pay-hero-amount">S/ {{ number_format($latestPayment->total_amount, 2) }}</div>
        <div class="pay-hero-meta">
            <x-payment-status :status="$latestPayment->status" />
            @if($latestPayment->status === 'pagado' && $latestPayment->payment_date)
            <span>Pagado el {{ $latestPayment->payment_date->format('d/m/Y') }}</span>
            @endif
            <span>{{ number_format($latestPayment->liters_collected, 0) }} L acopiados en el mes</span>
        </div>
    </div>
    <div class="pay-hero-actions">
        <a href="{{ route('collector.payment-receipt', $latestPayment) }}" class="btn-icon"><x-icon name="eye" /> Ver boleta</a>
        <a href="{{ route('collector.payment-receipt', ['collectorPayment' => $latestPayment, 'download' => 1]) }}" class="btn-icon light"><x-icon name="download" /> Descargar</a>
    </div>
</section>
@endif

<div class="pay-kpis">
    <div class="pay-kpi">
        <div class="pay-kpi-icon green"><x-icon name="wallet" /></div>
        <div>
            <div class="pay-kpi-label">Sueldo mensual</div>
            <div class="pay-kpi-value">{{ $summary['monthly_salary'] > 0 ? 'S/ '.number_format($summary['monthly_salary'], 2) : 'Por definir' }}</div>
            <div class="pay-kpi-hint">Pago fijo, no depende de los litros</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon blue"><x-icon name="check" /></div>
        <div>
            <div class="pay-kpi-label">Recibido en {{ now()->year }}</div>
            <div class="pay-kpi-value">S/ {{ number_format($summary['paid_this_year'], 2) }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon amber"><x-icon name="clock" /></div>
        <div>
            <div class="pay-kpi-label">Pendiente de cobro</div>
            <div class="pay-kpi-value">S/ {{ number_format($summary['pending'], 2) }}</div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Historial de boletas</div>
    </div>
    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr><th>Boleta</th><th>Mes</th><th>Sueldo</th><th>Neto</th><th>Estado</th><th style="text-align:right">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td><code class="badge badge-gray">{{ $p->receipt_number }}</code></td>
                    <td><strong>{{ $p->period_label }}</strong></td>
                    <td>S/ {{ number_format($p->base_salary, 2) }}</td>
                    <td class="pay-amount">S/ {{ number_format($p->total_amount, 2) }}</td>
                    <td>
                        <x-payment-status :status="$p->status" />
                        @if($p->status === 'pagado' && $p->payment_date)
                        <div class="pay-muted" style="margin-top:4px">{{ $p->payment_date->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="pay-actions">
                            <a href="{{ route('collector.payment-receipt', $p) }}" class="btn-icon"><x-icon name="eye" /> Ver</a>
                            <a href="{{ route('collector.payment-receipt', ['collectorPayment' => $p, 'download' => 1]) }}" class="btn-icon primary"><x-icon name="download" /> Descargar</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <div class="empty-icon">💼</div>
                            <h3>Aún no tienes boletas</h3>
                            <p>Cuando la administración genere la planilla del mes, tu boleta aparecerá aquí.</p>
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
@endsection
