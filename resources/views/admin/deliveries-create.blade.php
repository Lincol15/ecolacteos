@extends('layouts.app')

@section('page-title', 'Registrar Entrega de Leche')
@section('page-subtitle', 'Registro manual de acopio (Administración / Gerencia)')

@section('top-actions')
    <a href="{{ route('admin.deliveries') }}" class="btn btn-ghost">← Volver a Entregas</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🥛 Nueva Entrega</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.deliveries-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Productor *</label>
                    <select name="producer_id" required class="form-select">
                        <option value="">Seleccionar productor...</option>
                        @foreach($producers ?? [] as $p)
                        <option value="{{ $p->id }}" {{ old('producer_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->user?->fullname }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Acopiador</label>
                    <select name="collector_id" class="form-select">
                        <option value="">Sin acopiador (registrado por administración)</option>
                        @foreach($collectors ?? [] as $c)
                        <option value="{{ $c->id }}" {{ old('collector_id') == $c->id ? 'selected' : '' }}>{{ $c->fullname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Litros *</label>
                    <input type="number" step="0.01" min="0.1" name="liters" value="{{ old('liters') }}" required class="form-input" placeholder="Ej: 45.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Entrega *</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required class="form-input" max="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Temperatura (°C)</label>
                    <input type="number" step="0.01" name="temperature" value="{{ old('temperature') }}" class="form-input" placeholder="4.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Precio por Litro (S/)</label>
                    <input type="number" step="0.01" name="price_per_liter" value="{{ old('price_per_liter') }}" class="form-input" placeholder="Precio configurado por defecto">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Envase</label>
                    <select name="container_type" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <option value="caneca" {{ old('container_type') === 'caneca' ? 'selected' : '' }}>Caneca</option>
                        <option value="bidon" {{ old('container_type') === 'bidon' ? 'selected' : '' }}>Bidón</option>
                        <option value="cisterna" {{ old('container_type') === 'cisterna' ? 'selected' : '' }}>Cisterna</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">N° de Envases</label>
                    <input type="number" min="1" name="containers_count" value="{{ old('containers_count') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Placa Vehículo</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" class="form-input">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observations" class="form-textarea">{{ old('observations') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.deliveries') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Registrar Entrega</button>
            </div>
        </form>
    </div>
</div>
@endsection
