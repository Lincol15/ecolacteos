@props(['report', 'action', 'hidden' => []])

@php($isSingleDay = $report['from']->isSameDay($report['to']))

<div class="panel">
    <div class="panel-header" style="flex-wrap:wrap; gap:10px;">
        <div>
            <div class="panel-title">🥛 Leche recibida por día</div>
            <div class="form-hint" style="margin-top:4px">Litros entregados por los acopiadores (sin contar las entregas rechazadas).</div>
        </div>
    </div>
    <form method="GET" action="{{ $action }}" class="pay-toolbar">
        @foreach($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <div class="form-group">
            <label class="form-label" for="milk_from">Desde</label>
            <input type="date" name="milk_from" id="milk_from" value="{{ $report['from']->toDateString() }}" max="{{ now()->toDateString() }}" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label" for="milk_to">Hasta</label>
            <input type="date" name="milk_to" id="milk_to" value="{{ $report['to']->toDateString() }}" max="{{ now()->toDateString() }}" class="form-input">
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            <a href="{{ $action }}{{ $hidden ? '?'.http_build_query($hidden) : '' }}" class="btn btn-ghost btn-sm">Hoy</a>
            <a href="{{ $action }}?{{ http_build_query([...$hidden, 'milk_from' => now()->subDays(6)->toDateString(), 'milk_to' => now()->toDateString()]) }}" class="btn btn-ghost btn-sm">7 días</a>
            <a href="{{ $action }}?{{ http_build_query([...$hidden, 'milk_from' => now()->startOfMonth()->toDateString(), 'milk_to' => now()->toDateString()]) }}" class="btn btn-ghost btn-sm">Este mes</a>
        </div>
    </form>

    <div class="panel-body">
        <div class="milk-total">
            <div>
                <div class="pay-kpi-label">
                    {{ $isSingleDay
                        ? ($report['to']->isToday() ? 'Recibido hoy' : 'Recibido el '.$report['to']->format('d/m/Y'))
                        : 'Recibido del '.$report['from']->format('d/m/Y').' al '.$report['to']->format('d/m/Y') }}
                </div>
                <div class="pay-kpi-value">{{ number_format($report['total_liters'], 2) }} L</div>
            </div>
            <div class="pay-muted">{{ $report['total_deliveries'] }} entrega(s)@unless($isSingleDay) · {{ $report['days']->count() }} días @endunless</div>
        </div>

        @unless($isSingleDay)
        <div class="table-wrap" style="max-height:340px; overflow-y:auto; margin-top:14px;">
            <table>
                <thead><tr><th>Día</th><th>Entregas</th><th style="text-align:right">Litros</th></tr></thead>
                <tbody>
                    @foreach($report['days'] as $day)
                    <tr>
                        <td>{{ ucfirst($day['date']->locale('es')->translatedFormat('l d/m/Y')) }}</td>
                        <td>{{ $day['deliveries'] }}</td>
                        <td style="text-align:right; font-weight:700; {{ $day['liters'] > 0 ? '' : 'color:var(--text-light)' }}">{{ number_format($day['liters'], 2) }} L</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endunless
    </div>
</div>

<style>
    .milk-total { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap; padding: 16px 18px; border-radius: 14px; background: #EFF6FF; border: 1px solid #D1E3FA; }
</style>
