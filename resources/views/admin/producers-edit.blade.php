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
            <div style="background:#f8fafc;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;gap:24px">
                <div><div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">Usuario Asociado</div><div style="font-weight:700">{{ $producer->user?->fullname ?? 'N/A' }}</div></div>
                <div><div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase">DNI</div><div style="font-weight:700">{{ $producer->user?->dni ?? '-' }}</div></div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Código Productor (id_productor) *</label>
                    <input type="text" name="code" value="{{ old('code', $producer->code) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de Finca</label>
                    <input type="text" name="farm_name" value="{{ old('farm_name', $producer->farm_name) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Comunidad *</label>
                    <input type="text" name="comunidad" value="{{ old('comunidad', $producer->comunidad) }}" required class="form-input" placeholder="Ej: Huata Centro">
                </div>
                <div class="form-group">
                    <label class="form-label">Zona</label>
                    <input type="text" name="zone" value="{{ old('zone', $producer->zone) }}" class="form-input" placeholder="Ej: Valle Central">
                </div>
                <div class="form-group">
                    <label class="form-label">Distrito</label>
                    <input type="text" name="district" value="{{ old('district', $producer->district) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Provincia</label>
                    <input type="text" name="province" value="{{ old('province', $producer->province) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Región</label>
                    <input type="text" name="region" value="{{ old('region', $producer->region) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Cantidad de Vacas</label>
                    <input type="number" name="cows_count" value="{{ old('cows_count', $producer->cows_count) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Promedio Litros/Día</label>
                    <input type="number" step="0.01" name="daily_avg_liters" value="{{ old('daily_avg_liters', $producer->daily_avg_liters) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Registro</label>
                    <input type="date" name="registration_date" value="{{ old('registration_date', $producer->registration_date?->format('Y-m-d')) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado (activo/inactivo) *</label>
                    <select name="status" required class="form-select">
                        <option value="activo" {{ old('status', $producer->status) == 'activo' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="inactivo" {{ old('status', $producer->status) == 'inactivo' ? 'selected' : '' }}>❌ Inactivo (dar de baja)</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1 / -1">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $producer->notes) }}</textarea>
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
