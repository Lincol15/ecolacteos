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
                <div class="form-group" id="producerField" style="display:none;">
                    <label class="form-label">ID Productor Asociado</label>
                    <select name="producer_id" class="form-select">
                        <option value="">Sin asociar</option>
                        @forelse($producers ?? [] as $prod)
                        <option value="{{ $prod->id }}" {{ old('producer_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->code }} - {{ $prod->farm_name }}
                        </option>
                        @empty
                        <option value="">No hay productores disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="status" required class="form-select">
                        <option value="activo" {{ old('status', 'activo') == 'activo' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>❌ Inactivo</option>
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
    document.getElementById('producerField').style.display = this.value === 'productor' ? 'block' : 'none';
});
</script>
@endsection
