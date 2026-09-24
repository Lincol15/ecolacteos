@extends('layouts.app')

@section('page-title', 'Liquidación '.$payment->receipt_number)
@section('page-subtitle', 'Del '.$payment->period_start?->format('d/m/Y').' al '.$payment->period_end?->format('d/m/Y'))

@section('top-actions')
    <a href="{{ route('producer.payments') }}" class="btn btn-ghost">← Mis pagos</a>
@endsection

@section('content')
<section class="pay-hero">
    <div>
        <div class="pay-hero-label">Total neto a recibir</div>
        <div class="pay-hero-amount">S/ {{ number_format($payment->total_amount, 2) }}</div>
        <div class="pay-hero-meta">
            <x-payment-status :status="$payment->status" />
            <span>Método: {{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? '—' }}</span>
            @if($payment->status === 'pagado' && $payment->payment_date)
            <span>Pagado el {{ $payment->payment_date->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>
    <div class="pay-hero-actions">
        <a href="{{ route('producer.payment-receipt', $payment) }}" class="btn-icon"><x-icon name="printer" /> Ver comprobante</a>
        <a href="{{ route('producer.payment-receipt', ['payment' => $payment, 'download' => 1]) }}" class="btn-icon light"><x-icon name="download" /> Descargar PDF</a>
    </div>
</section>

<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Cómo se calculó tu pago</div>
        </div>
        <div class="panel-body">
            <div class="pay-breakdown">
                <div class="pay-line">
                    <span>Leche entregada: {{ number_format($payment->total_liters, 2) }} L × S/ {{ number_format($payment->precio_por_litro ?? $payment->avg_price_per_liter, 2) }}</span>
                    <strong>S/ {{ number_format($payment->base_amount, 2) }}</strong>
                </div>
                <div class="pay-line">
                    <span>Bono por calidad</span>
                    <strong class="pay-plus">+ S/ {{ number_format($payment->quality_bonus, 2) }}</strong>
                </div>
                <div class="pay-line">
                    <span>Bono por volumen</span>
                    <strong class="pay-plus">+ S/ {{ number_format($payment->production_bonus, 2) }}</strong>
                </div>
                <div class="pay-line">
                    <span>{{ $payment->deductions_detail ?? 'Deducciones' }}</span>
                    <strong class="pay-minus">− S/ {{ number_format($payment->deductions, 2) }}</strong>
                </div>
                @if($payment->llevado_a_planta > 0)
                <div class="pay-line">
                    <span>Leche llevada directo a planta</span>
                    <strong class="pay-minus">− S/ {{ number_format($payment->llevado_a_planta, 2) }}</strong>
                </div>
                @endif
                <div class="pay-line total">
                    <span>Total neto</span>
                    <strong>S/ {{ number_format($payment->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Litros por día</div>
        </div>
        <div class="panel-body">
            @if(!empty($payment->detalle_diario))
            <div class="pay-days">
                @foreach($payment->detalle_diario as $day => $liters)
                <div class="pay-day">
                    <small>{{ mb_substr($day, 0, 3) }}</small>
                    <strong>{{ number_format($liters, 1) }}</strong>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty" style="padding:20px"><p>Sin detalle diario para esta liquidación.</p></div>
            @endif

            @if($payment->status === 'pagado')
            <div class="pay-breakdown" style="margin-top:20px">
                <div class="pay-line"><span>N° de operación</span><strong>{{ $payment->transaction_number ?? '—' }}</strong></div>
                <div class="pay-line"><span>Fecha de pago</span><strong>{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</strong></div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Entregas incluidas ({{ $payment->items->count() }})</div>
    </div>
    <div class="table-wrap">
        <table class="pay-table">
            <thead><tr><th>Fecha</th><th>Hora</th><th>Litros</th><th>Precio/L</th><th>Calidad</th><th style="text-align:right">Importe</th></tr></thead>
            <tbody>
                @forelse($payment->items as $item)
                <tr>
                    <td>{{ $item->milkDelivery?->delivery_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $item->milkDelivery?->delivery_time?->format('H:i') ?? '—' }}</td>
                    <td><strong>{{ number_format($item->liters, 2) }} L</strong></td>
                    <td>S/ {{ number_format($item->price_per_liter, 2) }}</td>
                    <td>
                        @if($item->milkDelivery?->qualityReport)
                        <span class="badge {{ $item->milkDelivery->qualityReport->result === 'aprobado' ? 'badge-green' : 'badge-amber' }}">{{ ucfirst($item->milkDelivery->qualityReport->result) }}</span>
                        @else
                        <span class="pay-muted">Sin análisis</span>
                        @endif
                    </td>
                    <td class="pay-amount" style="text-align:right">S/ {{ number_format($item->line_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty"><h3>Sin detalle de entregas</h3></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
