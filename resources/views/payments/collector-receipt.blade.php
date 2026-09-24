@extends('payments.receipt-layout')

@section('title', 'Boleta '.$payment->receipt_number)
@section('doc-type', 'Boleta de pago mensual')
@section('doc-number', $payment->receipt_number)

@section('body')
@php($collector = $payment->collector)
<div class="grid">
    <div class="box">
        <h4>Acopiador</h4>
        <p>
            <strong>{{ $collector?->fullname ?? '—' }}</strong><br>
            DNI: {{ $collector?->dni ?? '—' }}<br>
            Comunidad: {{ $collector?->comunidad ?? '—' }}<br>
            Vehículo: {{ $collector?->vehiculo ?? '—' }}
        </p>
    </div>
    <div class="box">
        <h4>Período</h4>
        <p>
            <strong>{{ $payment->period_label }}</strong><br>
            Modalidad: sueldo fijo mensual<br>
            Acopio del mes: {{ number_format($payment->liters_collected, 1) }} L en {{ $payment->deliveries_count }} entregas
        </p>
    </div>
    <div class="box">
        <h4>Estado del pago</h4>
        <p>
            <span class="status {{ $payment->status }}">{{ \App\Models\Payment::STATUS[$payment->status] ?? ucfirst($payment->status) }}</span><br>
            Método: {{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? '—' }}<br>
            @if($payment->status === 'pagado')
            Fecha: {{ $payment->payment_date?->format('d/m/Y') }}<br>
            N° operación: {{ $payment->transaction_number ?? '—' }}
            @endif
        </p>
    </div>
</div>

<table>
    <thead><tr><th>Concepto</th><th class="num">Ingresos</th><th class="num">Descuentos</th></tr></thead>
    <tbody>
        <tr><td>Sueldo básico mensual</td><td class="num">S/ {{ number_format($payment->base_salary, 2) }}</td><td class="num">—</td></tr>
        @if($payment->bonus > 0)
        <tr><td>Bonificación</td><td class="num">S/ {{ number_format($payment->bonus, 2) }}</td><td class="num">—</td></tr>
        @endif
        @if($payment->deductions > 0)
        <tr><td>Descuentos</td><td class="num">—</td><td class="num">S/ {{ number_format($payment->deductions, 2) }}</td></tr>
        @endif
    </tbody>
</table>

<div class="summary">
    <div class="line"><span>Total ingresos</span><strong>S/ {{ number_format($payment->base_salary + $payment->bonus, 2) }}</strong></div>
    <div class="line"><span>Total descuentos</span><strong class="minus">− S/ {{ number_format($payment->deductions, 2) }}</strong></div>
    <div class="total"><span>Neto a pagar</span><strong>S/ {{ number_format($payment->total_amount, 2) }}</strong></div>
</div>

<div class="signatures">
    <div class="signature">Ecolácteos Huata</div>
    <div class="signature">{{ $collector?->fullname ?? 'Acopiador' }}</div>
</div>
@endsection
