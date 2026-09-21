@extends('layouts.app')

@section('page-title', 'Actualizar Precio de Leche')
@section('page-subtitle', 'Precio pagado por litro de leche fresca')

@section('content')
<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Precio Actual por Litro</div>
        <div class="stat-value green">S/ {{ number_format($config->casted_value, 2) }}</div>
        <div class="stat-icon-wrap green">💲</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Última Actualización</div>
        <div class="stat-value blue" style="font-size:16px">{{ $config->updated_at?->format('d/m/Y H:i') ?? 'Nunca' }}</div>
        <div class="stat-icon-wrap blue">🕐</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">💲 Actualizar Precio por Litro</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.price-update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nuevo Precio (S/ por litro) *</label>
                    <input type="number" step="0.01" min="0.1" max="100" name="value" value="{{ old('value', $config->casted_value) }}" required class="form-input">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar Nuevo Precio</button>
            </div>
        </form>
    </div>
</div>
@endsection
