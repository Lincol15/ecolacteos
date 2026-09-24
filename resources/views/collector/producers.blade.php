@extends('layouts.app')

@section('page-title', 'Mis Productores')
@section('page-subtitle', 'Productores que te asignó el administrador')

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">👥 Productores Asignados</div>
        <span class="badge badge-blue">Total: {{ $producers->count() }}</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Código</th><th>Productor</th><th>Zona / Comunidad</th><th>Litros del Mes</th></tr></thead>
            <tbody>
                @forelse($producers as $p)
                <tr>
                    <td><code class="badge badge-blue">{{ $p->code }}</code></td>
                    <td><strong>{{ $p->user?->fullname ?? 'N/A' }}</strong></td>
                    <td>{{ $p->zone }} @if($p->comunidad) &middot; {{ $p->comunidad }} @endif</td>
                    <td style="font-weight:700;color:#059669">{{ number_format($p->month_liters ?? 0, 2) }} L</td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty"><div class="empty-icon">👥</div><h3>Sin productores asignados</h3><p>El administrador aún no te asignó una comunidad ni productores.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
