@extends('layouts.app')

@section('page-title', 'Mis Pagos')
@section('page-subtitle', 'Tus liquidaciones por litro de leche entregada')

@section('content')
@if($latestPayment)
<section class="pay-hero">
    <div>
        <div class="pay-hero-label">Última liquidación · {{ $latestPayment->period_start?->format('d/m') }} al {{ $latestPayment->period_end?->format('d/m/Y') }}</div>
        <div class="pay-hero-amount">S/ {{ number_format($latestPayment->total_amount, 2) }}</div>
        <div class="pay-hero-meta">
            <x-payment-status :status="$latestPayment->status" />
            <span>{{ number_format($latestPayment->total_liters, 1) }} L × S/ {{ number_format($latestPayment->precio_por_litro ?? $latestPayment->avg_price_per_liter, 2) }}</span>
        </div>
    </div>
    <div class="pay-hero-actions">
        <a href="{{ route('producer.payment-show', $latestPayment) }}" class="btn-icon"><x-icon name="eye" /> Ver detalle</a>
        <a href="{{ route('producer.payment-receipt', ['payment' => $latestPayment, 'download' => 1]) }}" class="btn-icon light"><x-icon name="download" /> Descargar</a>
    </div>
</section>
@endif

<div class="pay-kpis">
    <div class="pay-kpi">
        <div class="pay-kpi-icon green"><x-icon name="check" /></div>
        <div>
            <div class="pay-kpi-label">Total recibido</div>
            <div class="pay-kpi-value">S/ {{ number_format($summary['total_pagado'], 2) }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon amber"><x-icon name="clock" /></div>
        <div>
            <div class="pay-kpi-label">Pendiente de cobro</div>
            <div class="pay-kpi-value">S/ {{ number_format($summary['total_pendiente'], 2) }}</div>
            <div class="pay-kpi-hint">Día de pago: {{ \App\Models\PlantConfig::getValue('dias_pago_semanal', 'Viernes') }}</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon blue"><x-icon name="milk" /></div>
        <div>
            <div class="pay-kpi-label">Litros liquidados</div>
            <div class="pay-kpi-value">{{ number_format($summary['total_liters'], 0) }} L</div>
        </div>
    </div>
    <div class="pay-kpi">
        <div class="pay-kpi-icon purple"><x-icon name="shield" /></div>
        <div>
            <div class="pay-kpi-label">Bonos ganados</div>
            <div class="pay-kpi-value">S/ {{ number_format($summary['total_bonos'], 2) }}</div>
        </div>
    </div>
</div>

<div class="panel">
    <form method="GET" action="{{ route('producer.payments') }}" class="pay-toolbar">
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
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="{{ route('producer.payments') }}" class="btn btn-ghost btn-sm">Limpiar</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="pay-table">
            <thead>
                <tr>
                    <th>Liquidación</th>
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
                @forelse($payments as $p)
                <tr>
                    <td><code class="badge badge-gray">{{ $p->receipt_number }}</code></td>
                    <td>
                        <div style="font-weight:600">{{ $p->period_start?->format('d/m') }} – {{ $p->period_end?->format('d/m/Y') }}</div>
                        <div class="pay-muted">{{ $p->period_code }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ number_format($p->total_liters, 1) }} L</div>
                        <div class="pay-muted">× S/ {{ number_format($p->precio_por_litro ?? $p->avg_price_per_liter, 2) }}</div>
                    </td>
                    <td class="pay-plus">+ S/ {{ number_format($p->bonus_total, 2) }}</td>
                    <td class="pay-minus">− S/ {{ number_format($p->discount_total, 2) }}</td>
                    <td class="pay-amount">S/ {{ number_format($p->total_amount, 2) }}</td>
                    <td><x-payment-status :status="$p->status" /></td>
                    <td>
                        <div class="pay-actions">
                            <a href="{{ route('producer.payment-show', $p) }}" class="btn-icon"><x-icon name="eye" /> Ver</a>
                            <a href="{{ route('producer.payment-receipt', ['payment' => $p, 'download' => 1]) }}" class="btn-icon primary"><x-icon name="download" /> Descargar</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty">
                            <div class="empty-icon">💳</div>
                            <h3>Aún no tienes liquidaciones</h3>
                            <p>Cuando la planta liquide tus entregas de leche aparecerán aquí.</p>
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
