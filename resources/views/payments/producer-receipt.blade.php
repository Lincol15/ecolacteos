@extends('payments.receipt-layout')

@section('title', 'Liquidación '.$payment->receipt_number)
@section('doc-type', 'Liquidación de pago por leche')
@section('doc-number', $payment->receipt_number)

@section('body')
@php($producer = $payment->producer)
<div class="grid">
    <div class="box">
        <h4>Productor</h4>
        <p>
            <strong>{{ $producer?->user?->fullname ?? '—' }}</strong><br>
            Código: {{ $producer?->code ?? '—' }}<br>
            DNI: {{ $producer?->user?->dni ?? '—' }}<br>
            Comunidad: {{ $producer?->comunidad ?? $producer?->zone ?? '—' }}
        </p>
    </div>
    <div class="box">
        <h4>Período liquidado</h4>
        <p>
            <strong>{{ $payment->period_start?->format('d/m/Y') }} al {{ $payment->period_end?->format('d/m/Y') }}</strong><br>
            Código: {{ $payment->period_code }}<br>
            Precio por litro: S/ {{ number_format($payment->precio_por_litro ?? $payment->avg_price_per_liter, 2) }}
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
    <thead>
        <tr><th>Fecha</th><th>Hora</th><th class="num">Litros</th><th class="num">Precio/L</th><th class="num">Importe</th></tr>
    </thead>
    <tbody>
        @forelse($payment->items as $item)
        <tr>
            <td>{{ $item->milkDelivery?->delivery_date?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $item->milkDelivery?->delivery_time?->format('H:i') ?? '—' }}</td>
            <td class="num">{{ number_format($item->liters, 2) }}</td>
            <td class="num">S/ {{ number_format($item->price_per_liter, 2) }}</td>
            <td class="num">S/ {{ number_format($item->line_amount, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted)">Detalle de entregas no disponible.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary">
    <div class="line"><span>Leche entregada ({{ number_format($payment->total_liters, 2) }} L)</span><strong>S/ {{ number_format($payment->base_amount, 2) }}</strong></div>
    @if($payment->quality_bonus > 0)
    <div class="line"><span>Bono por calidad</span><strong class="plus">+ S/ {{ number_format($payment->quality_bonus, 2) }}</strong></div>
    @endif
    @if($payment->production_bonus > 0)
    <div class="line"><span>Bono por volumen</span><strong class="plus">+ S/ {{ number_format($payment->production_bonus, 2) }}</strong></div>
    @endif
    @if($payment->deductions > 0)
    <div class="line"><span>{{ $payment->deductions_detail ?? 'Deducciones' }}</span><strong class="minus">− S/ {{ number_format($payment->deductions, 2) }}</strong></div>
    @endif
    @if($payment->llevado_a_planta > 0)
    <div class="line"><span>Leche llevada directo a planta</span><strong class="minus">− S/ {{ number_format($payment->llevado_a_planta, 2) }}</strong></div>
    @endif
    <div class="total"><span>Total neto</span><strong>S/ {{ number_format($payment->total_amount, 2) }}</strong></div>
</div>

<div class="signatures">
    <div class="signature">Ecolácteos Huata</div>
    <div class="signature">{{ $producer?->user?->fullname ?? 'Productor' }}</div>
</div>
@endsection
