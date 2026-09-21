@props(['ingredient', 'milkLabel' => 'Auto: leche registrada por acopiadores (no rechazada) sin usar'])

@if($ingredient->is_milk)
    <div style="font-size:11px; color:var(--text-light); margin-top:4px;">{{ $milkLabel }}</div>
@elseif($ingredient->min_stock !== null && $ingredient->currentStock() < $ingredient->min_stock)
    <div class="badge badge-red" style="margin-top:6px;">⚠️ Bajo el mínimo ({{ number_format($ingredient->min_stock, 2) }} {{ $ingredient->unit }})</div>
@endif
