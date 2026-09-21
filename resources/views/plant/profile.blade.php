@extends('layouts.app')

@section('title', 'Mi Perfil - Ecolácteos Huata')
@section('page-title', 'Mi Perfil')
@section('page-subtitle', 'Actualiza tus datos personales')

@section('content')
<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👤 Información Personal</div>
        </div>
        <div class="panel-body">
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#ede9fe,#fef3c7);border-radius:16px">
                <div style="width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:800;color:#fff;margin-bottom:14px;box-shadow:0 8px 24px rgba(139,92,246,0.3)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->lastname ?? '', 0, 1)) }}
                </div>
                <div style="font-size:22px;font-weight:800">{{ auth()->user()->fullname }}</div>
                <div style="color:#64748b;font-weight:500;margin-top:4px">
                    <span class="badge badge-purple">{{ auth()->user()->roleLabel }}</span>
                </div>
                <div style="color:#94a3b8;font-size:13px;margin-top:8px">Registrado: {{ auth()->user()->created_at?->format('d/m/Y') }}</div>
            </div>

            <form method="POST" action="{{ route('plant.profile-update') }}">
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
                    <a href="{{ route('plant.dashboard') }}" class="btn btn-ghost">Cancelar</a>
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
                <div class="panel-title">🏭 Estadísticas de Planta</div>
            </div>
            <div class="panel-body">
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:16px">
                    <div style="background:#f0fdf4;padding:14px;border-radius:10px;text-align:center">
                        <div style="font-size:11px;color:#065f46;font-weight:700;text-transform:uppercase">Lotes Gestionados</div>
                        <div style="font-size:24px;font-weight:900;color:#059669;margin-top:4px">{{ $statsBatches ?? 0 }}</div>
                    </div>
                    <div style="background:#eff6ff;padding:14px;border-radius:10px;text-align:center">
                        <div style="font-size:11px;color:#1e40af;font-weight:700;text-transform:uppercase">Mov. Inventario</div>
                        <div style="font-size:24px;font-weight:900;color:#2563eb;margin-top:4px">{{ $statsMovements ?? 0 }}</div>
                    </div>
                </div>
                <div class="form-actions" style="border:none;padding:0;margin:0;justify-content:center">
                    <a href="{{ route('plant.batches') }}" class="btn btn-purple">📦 Ver mis lotes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
