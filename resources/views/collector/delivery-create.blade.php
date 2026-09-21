@extends('layouts.app')

@section('title', 'Registrar Entrega - Ecolácteos Huata')
@section('page-title', 'Registrar Entrega de Leche')
@section('page-subtitle', 'Nueva entrega de leche recolectada al productor')

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚛 Formulario de Entrega</div>
        <a href="{{ route('collector.deliveries') }}" class="btn btn-sm btn-ghost">← Volver a Entregas</a>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('collector.delivery-store') }}">
            @csrf

            @if(request('route_stop_id') || request('collection_route_id'))
            <div style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:12px;padding:14px 18px;margin-bottom:24px;border-left:4px solid #3b82f6">
                <div style="font-weight:700;color:#1e40af;font-size:14px;margin-bottom:4px">📍 Entrega vinculada a Ruta</div>
                @if(request('collection_route_id'))
                <div style="font-size:13px;color:#3b82f6">Ruta ID: #{{ request('collection_route_id') }}</div>
                @endif
                @if(request('route_stop_id'))
                <div style="font-size:13px;color:#3b82f6">Parada ID: #{{ request('route_stop_id') }}</div>
                @endif
            </div>
            @endif

            <input type="hidden" name="route_stop_id" value="{{ request('route_stop_id') }}">
            <input type="hidden" name="collection_route_id" value="{{ request('collection_route_id') }}">

            <div style="background:#f0fdf4;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#065f46;margin-bottom:14px">👨‍🌾 Datos del Productor y Recolección</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Productor *</label>
                        <select name="producer_id" class="form-select" required>
                            <option value="">-- Seleccionar productor --</option>
                            @forelse($producers ?? [] as $p)
                            <option value="{{ $p->id }}" {{ old('producer_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->user?->fullname ?? 'Productor' }} ({{ $p->code ?? 'Sin código' }})
                            </option>
                            @empty
                            <option value="" disabled>Sin productores asignados — contacta al administrador</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha / Hora de Entrega *</label>
                        <input type="datetime-local" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d\TH:i')) }}" class="form-input" required>
                    </div>
                </div>
            </div>

            <div style="background:#eff6ff;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#1e40af;margin-bottom:14px">🥛 Datos de la Leche</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Litros Recolectados *</label>
                        <input type="number" step="0.01" name="liters" value="{{ old('liters') }}" class="form-input" required placeholder="50.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Temperatura (°C) *</label>
                        <input type="number" step="0.1" name="temperature" value="{{ old('temperature', 4.0) }}" class="form-input" required placeholder="4.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio por Litro (S/) *</label>
                        <input type="number" step="0.01" name="price_per_liter" value="{{ old('price_per_liter', 3.50) }}" class="form-input" required placeholder="3.50">
                    </div>
                </div>
            </div>

            <div style="background:#fef3c7;border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <div style="font-weight:700;color:#92400e;margin-bottom:14px">🚚 Detalles de Transporte</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo de Contenedor</label>
                        <select name="container_type" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <option value="bidon_plastico" {{ old('container_type') === 'bidon_plastico' ? 'selected' : '' }}>Bidón Plástico</option>
                            <option value="bidon_aluminio" {{ old('container_type') === 'bidon_aluminio' ? 'selected' : '' }}>Bidón Aluminio</option>
                            <option value="cisterna" {{ old('container_type') === 'cisterna' ? 'selected' : '' }}>Cisterna</option>
                            <option value="lata" {{ old('container_type') === 'lata' ? 'selected' : '' }}>Lata</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cantidad de Contenedores</label>
                        <input type="number" name="containers_count" value="{{ old('containers_count', 1) }}" class="form-input" min="1" placeholder="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Placa del Vehículo</label>
                        <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', auth()->user()->vehiculo ?? '') }}" class="form-input" placeholder="ABC-123">
                    </div>
                    <div class="form-group" style="grid-column:1 / -1">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observations" class="form-textarea" rows="3" placeholder="Notas sobre la entrega, estado de la leche, etc.">{{ old('observations') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('collector.dashboard') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Registrar Entrega</button>
            </div>
        </form>
    </div>
</div>
@endsection
