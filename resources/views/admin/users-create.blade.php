@extends('layouts.app')

@section('page-title', 'Crear Nuevo Usuario')
@section('page-subtitle', 'Registra un nuevo usuario en el sistema')

@section('top-actions')
    <a href="{{ route('admin.users') }}" class="btn btn-ghost">
        ← Volver al Listado
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">👤 Datos del Nuevo Usuario</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.user-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Juan">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="lastname" value="{{ old('lastname') }}" required class="form-input" placeholder="Ej: Pérez">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" value="{{ old('dni') }}" class="form-input" placeholder="Ej: 12345678">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="Ej: juan@correo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" required class="form-input" placeholder="Mínimo 8 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required class="form-input" placeholder="Repite la contraseña">
                </div>
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="role" required class="form-select" id="roleSelect">
                        <option value="">Selecciona un rol</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="gerente" {{ old('role') == 'gerente' ? 'selected' : '' }}>Gerente</option>
                        <option value="acopiador" {{ old('role') == 'acopiador' ? 'selected' : '' }}>Acopiador</option>
                        <option value="control_calidad" {{ old('role') == 'control_calidad' ? 'selected' : '' }}>Control Calidad</option>
                        <option value="trabajador_planta" {{ old('role') == 'trabajador_planta' ? 'selected' : '' }}>Trabajador Planta</option>
                        <option value="productor" {{ old('role') == 'productor' ? 'selected' : '' }}>Productor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="Ej: +51 987654321">
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="form-input" placeholder="Dirección del usuario">
                </div>
                <div class="form-group" id="comunidadField" style="display:none;">
                    <label class="form-label">Comunidad</label>
                    <input type="text" name="comunidad" value="{{ old('comunidad') }}" class="form-input" placeholder="Ej: Huata Centro">
                </div>
                <div class="form-group" id="vehiculoField" style="display:none;">
                    <label class="form-label">Vehículo (placa)</label>
                    <input type="text" name="vehiculo" value="{{ old('vehiculo') }}" class="form-input" placeholder="Ej: AB-1234">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="active" required class="form-select">
                        <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>❌ Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.users') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Usuario</button>
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
