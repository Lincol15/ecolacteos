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
                <div class="form-group" id="producerField" style="{{ old('role', $user->role) == 'productor' ? '' : 'display:none;' }}">
                    <label class="form-label">ID Productor Asociado</label>
                    <select name="producer_id" class="form-select">
                        <option value="">Sin asociar</option>
                        @forelse($producers ?? [] as $prod)
                        <option value="{{ $prod->id }}" {{ old('producer_id', $user->producer_id ?? '') == $prod->id ? 'selected' : '' }}>
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
                        <option value="activo" {{ old('status', $user->status) == 'activo' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="inactivo" {{ old('status', $user->status) == 'inactivo' ? 'selected' : '' }}>❌ Inactivo</option>
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
    document.getElementById('producerField').style.display = this.value === 'productor' ? 'block' : 'none';
});
</script>
@endsection
