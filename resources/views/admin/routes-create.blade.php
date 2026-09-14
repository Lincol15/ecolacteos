@extends('layouts.app')

@section('page-title', 'Crear Nueva Ruta')
@section('page-subtitle', 'Define una nueva ruta de acopio para recolección de leche')

@section('top-actions')
    <a href="{{ route('admin.routes') }}" class="btn btn-ghost">
        ← Volver a Rutas
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🗺️ Datos de la Nueva Ruta</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.routes-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre Ruta *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Ruta Norte - Sector Chosica">
                </div>
                <div class="form-group">
                    <label class="form-label">Código Ruta *</label>
                    <input type="text" name="code" value="{{ old('code', 'RUTA-'.str_pad(rand(1,999), 3, '0', STR_PAD_LEFT)) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Día de Recorrido *</label>
                    <select name="day" required class="form-select">
                        <option value="Lunes" {{ old('day') == 'Lunes' ? 'selected' : '' }}>Lunes</option>
                        <option value="Martes" {{ old('day') == 'Martes' ? 'selected' : '' }}>Martes</option>
                        <option value="Miércoles" {{ old('day') == 'Miércoles' ? 'selected' : '' }}>Miércoles</option>
                        <option value="Jueves" {{ old('day') == 'Jueves' ? 'selected' : '' }}>Jueves</option>
                        <option value="Viernes" {{ old('day') == 'Viernes' ? 'selected' : '' }}>Viernes</option>
                        <option value="Sábado" {{ old('day') == 'Sábado' ? 'selected' : '' }}>Sábado</option>
                        <option value="Domingo" {{ old('day') == 'Domingo' ? 'selected' : '' }}>Domingo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Inicio *</label>
                    <input type="time" name="start_time" value="{{ old('start_time', '06:00') }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Fin *</label>
                    <input type="time" name="end_time" value="{{ old('end_time', '12:00') }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Placa Vehículo</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" class="form-input" placeholder="Ej: ABC-123">
                </div>
                <div class="form-group">
                    <label class="form-label">Distancia Estimada (km)</label>
                    <input type="number" step="0.01" name="estimated_distance_km" value="{{ old('estimated_distance_km') }}" class="form-input" placeholder="Ej: 45.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Acopiador Asignado</label>
                    <select name="collector_id" class="form-select">
                        <option value="">Sin asignar</option>
                        @forelse($collectors ?? [] as $col)
                        <option value="{{ $col->id }}" {{ old('collector_id') == $col->id ? 'selected' : '' }}>
                            {{ $col->name }} {{ $col->lastname }}
                        </option>
                        @empty
                        <option value="">No hay acopiadores disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción / Detalle de Ruta</label>
                    <textarea name="description" class="form-textarea" placeholder="Puntos de recojo, observaciones, detalles del recorrido...">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="activa" {{ old('status', 'activa') == 'activa' ? 'selected' : '' }}>✅ Activa</option>
                        <option value="inactiva" {{ old('status') == 'inactiva' ? 'selected' : '' }}>❌ Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.routes') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Crear Ruta</button>
            </div>
        </form>
    </div>
</div>
@endsection
