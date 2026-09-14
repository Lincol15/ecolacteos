@extends('layouts.app')

@section('title', 'Mi Perfil - VACA SYS')
@section('page-title', 'Mi Perfil')
@section('page-subtitle', 'Actualiza tus datos personales y de vehículo')

@section('content')
<div class="grid-2">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">👤 Información Personal</div>
        </div>
        <div class="panel-body">
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:28px;padding:24px;background:linear-gradient(135deg,#cffafe,#a5f3fc);border-radius:16px">
                <div style="width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,#0891b2,#0e7490);display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:800;color:#fff;margin-bottom:14px;box-shadow:0 8px 24px rgba(8,145,178,0.3)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->lastname ?? '', 0, 1)) }}
                </div>
                <div style="font-size:22px;font-weight:800">{{ auth()->user()->fullname }}</div>
                <div style="color:#64748b;font-weight:500;margin-top:4px">
                    <span class="badge badge-cyan">{{ auth()->user()->roleLabel }}</span>
                </div>
                <div style="color:#94a3b8;font-size:13px;margin-top:8px">Registrado: {{ auth()->user()->created_at?->format('d/m/Y') }}</div>
            </div>

            <form method="POST" action="{{ route('collector.profile-update') }}">
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
                        <label class="form-label">Placa de Vehículo</label>
                        <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', auth()->user()->vehicle_plate ?? '') }}" class="form-input" placeholder="ABC-123">
                    </div>
                    <div class="form-group" style="grid-column:1 / -1">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" value="{{ auth()->user()->email }}" class="form-input" disabled style="background:#f1f5f9;cursor:not-allowed">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('collector.dashboard') }}" class="btn btn-ghost">Cancelar</a>
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
                <div class="panel-title">🚛 Estadísticas de Acopiador</div>
            </div>
            <div class="panel-body">
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:16px">
                    <div style="background:#cffafe;padding:14px;border-radius:10px;text-align:center">
                        <div style="font-size:11px;color:#155e75;font-weight:700;text-transform:uppercase">Total Litros</div>
                        <div style="font-size:24px;font-weight:900;color:#0891b2;margin-top:4px">{{ number_format($statsLiters ?? 0, 0) }} L</div>
                    </div>
                    <div style="background:#f0fdf4;padding:14px;border-radius:10px;text-align:center">
                        <div style="font-size:11px;color:#065f46;font-weight:700;text-transform:uppercase">Entregas</div>
                        <div style="font-size:24px;font-weight:900;color:#059669;margin-top:4px">{{ $statsDeliveries ?? 0 }}</div>
                    </div>
                </div>
                <div class="form-actions" style="border:none;padding:0;margin:0;justify-content:center">
                    <a href="{{ route('collector.deliveries') }}" class="btn btn-info">🚛 Ver mis entregas</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
