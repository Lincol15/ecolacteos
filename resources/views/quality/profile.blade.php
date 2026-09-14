@extends('layouts.app')

@section('title', 'Mi Perfil - VACA SYS')
@section('page-title', 'Mi Perfil')
@section('page-subtitle', 'Actualiza tus datos personales')

@section('content')
<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👤 Información Personal</div>
        </div>
        <div class="panel-body">
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#f0fdf4,#ecfeff);border-radius:16px">
                <div style="width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:800;color:#065f46;margin-bottom:14px;box-shadow:0 8px 24px rgba(251,191,36,0.3)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->lastname ?? '', 0, 1)) }}
                </div>
                <div style="font-size:22px;font-weight:800">{{ auth()->user()->fullname }}</div>
                <div style="color:#64748b;font-weight:500;margin-top:4px">
                    <span class="badge badge-purple">{{ auth()->user()->roleLabel }}</span>
                </div>
                <div style="color:#94a3b8;font-size:13px;margin-top:8px">Registrado: {{ auth()->user()->created_at?->format('d/m/Y') }}</div>
            </div>

            <form method="POST" action="{{ route('quality.profile-update') }}">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="lastname" value="{{ old('lastname', auth()->user()->lastname) }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="form-input" placeholder="+51 987 654 321">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" value="{{ auth()->user()->email }}" class="form-input" disabled style="background:#f1f5f9;cursor:not-allowed">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('quality.dashboard') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:24px">
            <div class="panel-header">
                <div class="panel-title">🔐 Cambiar Contraseña</div>
            </div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1 / -1">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-input" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-input" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar Nueva</label>
                        <input type="password" class="form-input" placeholder="••••••••">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-accent">🔑 Actualizar Contraseña</button>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📊 Mi Actividad Reciente</div>
            </div>
            <div class="panel-body" style="padding:0">
                <table>
                    <thead><tr><th>Acción</th><th>Detalle</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse($recentActivity ?? [] as $a)
                        <tr>
                            <td style="font-weight:600">{{ $a->action }}</td>
                            <td style="font-size:13px;color:#64748b">{{ $a->detail }}</td>
                            <td style="font-size:12px;color:#94a3b8">{{ $a->created_at?->format('d/m H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty" style="padding:30px 20px"><div class="empty-icon" style="font-size:40px">📋</div><h3 style="font-size:15px">Sin actividad reciente</h3></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
