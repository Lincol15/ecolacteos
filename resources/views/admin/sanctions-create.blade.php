@extends('layouts.app')

@section('page-title', 'Nueva Sanción')
@section('page-subtitle', 'Registrar una sanción a un productor')

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🚫 Formulario de Sanción</div>
        <a href="{{ route('admin.sanctions') }}" class="btn btn-sm btn-ghost">← Volver</a>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.sanctions-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Productor *</label>
                    <select name="producer_id" class="form-select" required>
                        <option value="">-- Seleccionar productor --</option>
                        @foreach($producers as $p)
                        <option value="{{ $p->id }}" {{ old('producer_id') == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->user?->fullname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="type" class="form-select" required>
                        @foreach(\App\Models\Sanction::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Sanción *</label>
                    <input type="date" name="sanction_date" value="{{ old('sanction_date', now()->toDateString()) }}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Monto (S/)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-input" placeholder="Opcional">
                </div>
                <div class="form-group" style="grid-column:1 / -1">
                    <label class="form-label">Motivo *</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" class="form-input" required maxlength="150" placeholder="Ej: Adulteración de leche con agua">
                </div>
                <div class="form-group" style="grid-column:1 / -1">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-textarea" rows="4">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.sanctions') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Sanción</button>
            </div>
        </form>
    </div>
</div>
@endsection
