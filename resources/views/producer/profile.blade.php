@extends('layouts.app')

@section('page-title', 'Mi Perfil')
@section('page-subtitle', 'Datos personales y de tu finca')

@section('content')
<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👤 Mis Datos</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('producer.profile-update') }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">DNI</label>
                        <input type="text" value="{{ $user->dni }}" class="form-input" disabled>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-input">
                    </div>
                </div>

                <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
                    <div style="font-weight:700; margin-bottom:14px;">🏡 Datos de la Finca</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre de la Finca</label>
                            <input type="text" name="farm_name" value="{{ old('farm_name', $producer->farm_name ?? '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Zona / Comunidad</label>
                            <input type="text" name="zone" value="{{ old('zone', $producer->zone ?? '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">N° de Vacas</label>
                            <input type="number" min="0" name="cows_count" value="{{ old('cows_count', $producer->cows_count ?? '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código de Productor</label>
                            <input type="text" value="{{ $producer->code ?? '-' }}" class="form-input" disabled>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">ℹ️ Información de Cuenta</div>
        </div>
        <div class="panel-body">
            <div style="padding:10px 0; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between;">
                <span style="color:var(--text-light); font-size:13px;">Correo</span>
                <strong style="font-size:13px;">{{ $user->email }}</strong>
            </div>
            <div style="padding:10px 0; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between;">
                <span style="color:var(--text-light); font-size:13px;">Rol</span>
                <strong style="font-size:13px;">{{ $user->roleLabel }}</strong>
            </div>
            <div style="padding:10px 0; display:flex; justify-content:space-between;">
                <span style="color:var(--text-light); font-size:13px;">Estado</span>
                <span class="badge {{ $user->active ? 'badge-green' : 'badge-red' }}">{{ $user->active ? 'Activo' : 'Inactivo' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
