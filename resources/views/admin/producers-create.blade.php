@extends('layouts.app')

@section('page-title', 'Nuevo Productor')
@section('page-subtitle', 'Registra un nuevo productor de leche en el sistema')

@section('top-actions')
    <a href="{{ route('admin.producers') }}" class="btn btn-ghost">
        ← Volver al Listado
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">👨‍🌾 Datos del Nuevo Productor</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.producer-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Juan">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="lastname" value="{{ old('lastname') }}" class="form-input" placeholder="Ej: Pérez">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI *</label>
                    <input type="text" name="dni" value="{{ old('dni') }}" required maxlength="8" pattern="\d{8}" class="form-input" placeholder="8 dígitos, ej: 12345678">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="9" pattern="\d{9}" class="form-input" placeholder="9 dígitos, ej: 987654321">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="Ej: productor@correo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Comunidad *</label>
                    <input type="text" name="comunidad" value="{{ old('comunidad') }}" required class="form-input" placeholder="Ej: Huata Centro">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de Finca</label>
                    <input type="text" name="farm_name" value="{{ old('farm_name') }}" class="form-input" placeholder="Ej: Finca El Rosal">
                </div>
                <div class="form-group">
                    <label class="form-label">Cantidad de Vacas</label>
                    <input type="number" name="cows_count" value="{{ old('cows_count') }}" min="0" class="form-input" placeholder="Ej: 10">
                </div>
                <div class="form-group">
                    <label class="form-label">Litros Promedio/Día</label>
                    <input type="number" step="0.01" name="daily_avg_liters" value="{{ old('daily_avg_liters') }}" min="0" class="form-input" placeholder="Ej: 45.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" required class="form-input" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required class="form-input" placeholder="Repite la contraseña">
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.producers') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Productor</button>
            </div>
        </form>
    </div>
</div>
@endsection
