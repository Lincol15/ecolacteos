@extends('layouts.app')

@section('page-title', 'Crear Nuevo Aviso')
@section('page-subtitle', 'Redactar y publicar comunicado')

@section('top-actions')
    <a href="{{ route('admin.notifications') }}" class="btn btn-ghost">
        ← Volver a Avisos
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📢 Redactar Comunicado</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.notifications-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Título del Aviso *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="form-input" style="font-size:16px; font-weight:600;" placeholder="Ej: Mantenimiento programado de planta">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Contenido / Mensaje *</label>
                    <textarea name="message" required class="form-textarea" style="min-height:180px;" placeholder="Escribe el contenido del aviso. Puedes usar saltos de línea.">{{ old('message') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirigido a *</label>
                    <select name="target" required class="form-select">
                        <option value="todos" {{ old('target') == 'todos' ? 'selected' : '' }}>👥 Todos los usuarios</option>
                        <option value="productores" {{ old('target') == 'productores' ? 'selected' : '' }}>👨‍🌾 Solo Productores</option>
                        <option value="acopiadores" {{ old('target') == 'acopiadores' ? 'selected' : '' }}>🚛 Solo Acopiadores</option>
                        <option value="gerencia" {{ old('target') == 'gerencia' ? 'selected' : '' }}>👔 Gerencia / Admin</option>
                        <option value="admin" {{ old('target') == 'admin' ? 'selected' : '' }}>👑 Solo Admin</option>
                    </select>
                    <small class="form-hint">Se ignora si eliges un usuario específico abajo.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Usuario Específico (opcional)</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Ninguno, usar "Dirigido a" --</option>
                        @foreach($users ?? [] as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->fullname }} ({{ $u->roleLabel }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad *</label>
                    <select name="priority" required class="form-select">
                        <option value="baja" {{ old('priority') == 'baja' ? 'selected' : '' }}>🟢 Baja (Informativo)</option>
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>🟡 Normal (Importante)</option>
                        <option value="alta" {{ old('priority') == 'alta' ? 'selected' : '' }}>🔴 Alta (Urgente lectura)</option>
                        <option value="urgente" {{ old('priority') == 'urgente' ? 'selected' : '' }}>🚨 Urgente (Requiere acción)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">¿Publicar Activo?</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>✅ Sí, publicar inmediatamente</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>⏸️ No, guardar como borrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha / Hora Publicación</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', date('Y-m-d\TH:i')) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Expiración (opcional)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="form-input">
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.notifications') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">📢 Publicar Aviso</button>
            </div>
        </form>
    </div>
</div>
@endsection
