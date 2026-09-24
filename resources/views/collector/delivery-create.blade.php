@extends('layouts.app')

@section('title', 'Registrar Acopio - Ecolácteos Huata')
@section('page-title', 'Registrar Acopio')
@section('page-subtitle', 'Registra los litros recolectados al productor')

@section('top-actions')
    <a href="{{ route('collector.deliveries') }}" class="btn btn-ghost">← Mis entregas</a>
@endsection

@section('content')
<div class="grid-2" style="align-items:start">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🥛 Nueva recolección</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('collector.delivery-store') }}">
                @csrf
                <input type="hidden" name="route_stop_id" value="{{ request('route_stop_id') }}">
                <input type="hidden" name="collection_route_id" value="{{ request('collection_route_id') }}">

                <div class="form-group">
                    <label class="form-label" for="producer_id">Productor *</label>
                    <select name="producer_id" id="producer_id" class="form-select" required>
                        <option value="">-- Seleccionar productor --</option>
                        @forelse($producers ?? [] as $p)
                        <option value="{{ $p->id }}" @selected(old('producer_id', $routeStop?->producer_id) == $p->id)>
                            {{ $p->user?->fullname ?? 'Productor' }} ({{ $p->code ?? 'Sin código' }})
                        </option>
                        @empty
                        <option value="" disabled>Sin productores asignados — contacta al administrador</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="liters">Litros recolectados *</label>
                    <input type="number" step="0.01" min="0.1" name="liters" id="liters" value="{{ old('liters') }}"
                           class="form-input" required placeholder="Ej: 50.00" inputmode="decimal" autofocus
                           style="font-size:22px; font-weight:700; height:56px;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="observations">Observaciones</label>
                    <textarea name="observations" id="observations" class="form-textarea" rows="3" maxlength="1000"
                              placeholder="Notas sobre la recolección, estado de la leche, etc. (opcional)">{{ old('observations') }}</textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('collector.dashboard') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">💾 Registrar Acopio</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📋 Datos automáticos</div>
        </div>
        <div class="panel-body">
            <div class="pay-breakdown">
                <div class="pay-line"><span>Fecha</span><strong>{{ now()->format('d/m/Y') }} · {{ now()->format('H:i') }}</strong></div>
                <div class="pay-line"><span>Vehículo</span><strong>{{ auth()->user()->vehiculo ?? 'Sin asignar' }}</strong></div>
                <div class="pay-line"><span>Precio por litro</span><strong>S/ {{ number_format($pricePerLiter, 2) }}</strong></div>
                <div class="pay-line total"><span>Importe estimado</span><strong id="estimatedTotal">S/ 0.00</strong></div>
            </div>
            <p class="form-hint" style="margin-top:14px;">
                El vehículo lo asigna el administrador y el precio por litro solo lo puede cambiar el administrador.
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    const liters = document.getElementById('liters');
    const total = document.getElementById('estimatedTotal');
    const price = {{ $pricePerLiter }};
    const update = () => {
        const value = parseFloat(liters.value) || 0;
        total.textContent = 'S/ ' + (value * price).toFixed(2);
    };
    liters.addEventListener('input', update);
    update();
})();
</script>
@endsection
