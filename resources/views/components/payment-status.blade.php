@props(['status'])

@php
    [$label, $variant] = match ($status) {
        'pagado' => ['Pagado', 'paid'],
        'pendiente' => ['Pendiente', 'pending'],
        'procesando' => ['Procesando', 'processing'],
        'parcial' => ['Parcial', 'processing'],
        'rechazado' => ['Rechazado', 'rejected'],
        default => [ucfirst((string) $status), 'rejected'],
    };
@endphp

<span {{ $attributes->merge(['class' => "status-pill status-{$variant}"]) }}>
    <span class="status-dot" aria-hidden="true"></span>{{ $label }}
</span>
