@extends('layouts.app')

@section('page-title', 'Editar Usuario')
@section('page-subtitle', 'Modifica los datos del usuario')

@section('top-actions')
    <a href="{{ route('admin.users') }}" class="btn btn-ghost">
        ← Volver al Listado
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">✏️ Editando: {{ $user->name }} {{ $user->lastname }}</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.user-update', $user) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" value="{{ old('dni', $user->dni) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Nueva Contraseña <small>(dejar vacío para mantener)</small></label>
                    <input type="password" name="password" class="form-input" placeholder="Mínimo 8 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Repite la nueva contraseña">
                </div>
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="role" required class="form-select" id="roleSelect">
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="gerente" {{ old('role', $user->role) == 'gerente' ? 'selected' : '' }}>Gerente</option>
                        <option value="acopiador" {{ old('role', $user->role) == 'acopiador' ? 'selected' : '' }}>Acopiador</option>
                        <option value="control_calidad" {{ old('role', $user->role) == 'control_calidad' ? 'selected' : '' }}>Control Calidad</option>
                        <option value="trabajador_planta" {{ old('role', $user->role) == 'trabajador_planta' ? 'selected' : '' }}>Trabajador Planta</option>
                        <option value="productor" {{ old('role', $user->role) == 'productor' ? 'selected' : '' }}>Productor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-input">
                </div>
                <div class="form-group" id="comunidadField" style="{{ in_array(old('role', $user->role), ['productor', 'acopiador']) ? '' : 'display:none;' }}">
                    <label class="form-label">Comunidad</label>
                    <input type="text" name="comunidad" value="{{ old('comunidad', $user->comunidad) }}" class="form-input" placeholder="Ej: Huata Centro">
                </div>
                <div class="form-group" id="vehiculoField" style="{{ old('role', $user->role) == 'acopiador' ? '' : 'display:none;' }}">
                    <label class="form-label">Vehículo (placa)</label>
                    <input type="text" name="vehiculo" value="{{ old('vehiculo', $user->vehiculo) }}" class="form-input" placeholder="Ej: AB-1234">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="active" required class="form-select">
                        <option value="1" {{ old('active', $user->active ? '1' : '0') == '1' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="0" {{ old('active', $user->active ? '1' : '0') == '0' ? 'selected' : '' }}>❌ Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.users') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Actualizar Usuario</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('comunidadField').style.display = ['productor', 'acopiador'].includes(this.value) ? 'block' : 'none';
    document.getElementById('vehiculoField').style.display = this.value === 'acopiador' ? 'block' : 'none';
});
</script>
@endsection
