@extends('layouts.app')

@section('page-title', 'Editar Productor')
@section('page-subtitle', 'Modifica los datos del productor')

@section('top-actions')
    <a href="{{ route('admin.producers') }}" class="btn btn-ghost">
        ← Volver al Listado
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🏡 Editando Finca: {{ $producer->farm_name }}</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.producer-update', $producer) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Código Productor *</label>
                    <input type="text" name="code" value="{{ old('code', $producer->code) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Usuario Asociado *</label>
                    <select name="user_id" required class="form-select">
                        <option value="">Seleccionar usuario...</option>
                        @forelse($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $producer->user_id) == $user->id ? 'selected' : '' }}>
                            #{{ $user->id }} - {{ $user->name }} {{ $user->lastname }}
                        </option>
                        @empty
                        <option value="">No hay usuarios disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de Finca *</label>
                    <input type="text" name="farm_name" value="{{ old('farm_name', $producer->farm_name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Zona *</label>
                    <input type="text" name="zone" value="{{ old('zone', $producer->zone) }}" required class="form-input" placeholder="Ej: Valle Central">
                </div>
                <div class="form-group">
                    <label class="form-label">Región</label>
                    <input type="text" name="region" value="{{ old('region', $producer->region) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Provincia</label>
                    <input type="text" name="province" value="{{ old('province', $producer->province) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Distrito</label>
                    <input type="text" name="district" value="{{ old('district', $producer->district) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Comunidad</label>
                    <input type="text" name="community" value="{{ old('community', $producer->community) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección Completa</label>
                    <input type="text" name="address" value="{{ old('address', $producer->address) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $producer->phone) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Hectáreas</label>
                    <input type="number" step="0.01" name="hectareas" value="{{ old('hectareas', $producer->hectareas) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Cantidad de Vacas</label>
                    <input type="number" name="cows_count" value="{{ old('cows_count', $producer->cows_count) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Promedio Litros/Día</label>
                    <input type="number" step="0.01" name="average_liters" value="{{ old('average_liters', $producer->average_liters) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Asociación</label>
                    <input type="text" name="association" value="{{ old('association', $producer->association) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Registro</label>
                    <input type="date" name="registration_date" value="{{ old('registration_date', $producer->registration_date ? \Carbon\Carbon::parse($producer->registration_date)->format('Y-m-d') : '') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Producción</label>
                    <select name="tipo_produccion" class="form-select">
                        <option value="convencional" {{ old('tipo_produccion', $producer->tipo_produccion) == 'convencional' ? 'selected' : '' }}>Convencional</option>
                        <option value="organico" {{ old('tipo_produccion', $producer->tipo_produccion) == 'organico' ? 'selected' : '' }}>Orgánico</option>
                        <option value="mixta" {{ old('tipo_produccion', $producer->tipo_produccion) == 'mixta' ? 'selected' : '' }}>Mixta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Certificación</label>
                    <input type="text" name="certification" value="{{ old('certification', $producer->certification) }}" class="form-input" placeholder="Ej: SGS, ISO 9001...">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="status" required class="form-select">
                        <option value="activo" {{ old('status', $producer->status) == 'activo' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="inactivo" {{ old('status', $producer->status) == 'inactivo' ? 'selected' : '' }}>❌ Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.producers') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
