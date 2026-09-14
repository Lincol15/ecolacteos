@extends('layouts.app')

@section('page-title', 'Editar Ruta')
@section('page-subtitle', 'Modifica los datos de la ruta de acopio')

@section('top-actions')
    <a href="{{ route('admin.routes') }}" class="btn btn-ghost">
        ← Volver a Rutas
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🗺️ Editando Ruta: {{ $route->name }}</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.routes-update', $route) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre Ruta *</label>
                    <input type="text" name="name" value="{{ old('name', $route->name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Código Ruta *</label>
                    <input type="text" name="code" value="{{ old('code', $route->code) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Día de Recorrido *</label>
                    <select name="day" required class="form-select">
                        <option value="Lunes" {{ old('day', $route->day) == 'Lunes' ? 'selected' : '' }}>Lunes</option>
                        <option value="Martes" {{ old('day', $route->day) == 'Martes' ? 'selected' : '' }}>Martes</option>
                        <option value="Miércoles" {{ old('day', $route->day) == 'Miércoles' ? 'selected' : '' }}>Miércoles</option>
                        <option value="Jueves" {{ old('day', $route->day) == 'Jueves' ? 'selected' : '' }}>Jueves</option>
                        <option value="Viernes" {{ old('day', $route->day) == 'Viernes' ? 'selected' : '' }}>Viernes</option>
                        <option value="Sábado" {{ old('day', $route->day) == 'Sábado' ? 'selected' : '' }}>Sábado</option>
                        <option value="Domingo" {{ old('day', $route->day) == 'Domingo' ? 'selected' : '' }}>Domingo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Inicio *</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $route->start_time) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Fin *</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $route->end_time) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Placa Vehículo</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $route->vehicle_plate) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Distancia Estimada (km)</label>
                    <input type="number" step="0.01" name="estimated_distance_km" value="{{ old('estimated_distance_km', $route->estimated_distance_km) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Acopiador Asignado</label>
                    <select name="collector_id" class="form-select">
                        <option value="">Sin asignar</option>
                        @forelse($collectors ?? [] as $col)
                        <option value="{{ $col->id }}" {{ old('collector_id', $route->collector_id) == $col->id ? 'selected' : '' }}>
                            {{ $col->name }} {{ $col->lastname }}
                        </option>
                        @empty
                        <option value="">No hay acopiadores disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción / Detalle de Ruta</label>
                    <textarea name="description" class="form-textarea">{{ old('description', $route->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="activa" {{ old('status', $route->status) == 'activa' ? 'selected' : '' }}>✅ Activa</option>
                        <option value="inactiva" {{ old('status', $route->status) == 'inactiva' ? 'selected' : '' }}>❌ Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.routes') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
