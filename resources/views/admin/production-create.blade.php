@extends('layouts.app')

@section('page-title', 'Crear Lote de Producción')
@section('page-subtitle', 'Registra un nuevo lote de producción en planta')

@section('top-actions')
    <a href="{{ route('admin.production') }}" class="btn btn-ghost">
        ← Volver a Producción
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🏭 Nuevo Lote de Producción</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.production-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Fecha de Producción *</label>
                    <input type="date" name="production_date" value="{{ old('production_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Producto *</label>
                    <select name="product_id" required class="form-select">
                        <option value="">Seleccionar producto...</option>
                        @forelse($products ?? [] as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                        @empty
                        <option value="">No hay productos disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Código Lote *</label>
                    <input type="text" name="batch_code" value="{{ old('batch_code', 'LOTE-'.date('Ymd').'-'.str_pad(rand(1,999), 3, '0', STR_PAD_LEFT)) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Cantidad Planificada *</label>
                    <input type="number" step="0.01" name="planned_quantity" value="{{ old('planned_quantity') }}" required class="form-input" placeholder="Ej: 500">
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad *</label>
                    <select name="unit" required class="form-select">
                        <option value="unidades" {{ old('unit') == 'unidades' ? 'selected' : '' }}>Unidades</option>
                        <option value="litros" {{ old('unit') == 'litros' ? 'selected' : '' }}>Litros</option>
                        <option value="kilos" {{ old('unit') == 'kilos' ? 'selected' : '' }}>Kilos</option>
                        <option value="cajas" {{ old('unit') == 'cajas' ? 'selected' : '' }}>Cajas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Leche Utilizada (L) *</label>
                    <input type="number" step="0.01" name="milk_used_liters" value="{{ old('milk_used_liters') }}" required class="form-input" placeholder="Ej: 1200">
                </div>
                <div class="form-group">
                    <label class="form-label">Supervisor *</label>
                    <select name="supervisor_id" required class="form-select">
                        <option value="">Seleccionar supervisor...</option>
                        @forelse($supervisors ?? [] as $sup)
                        <option value="{{ $sup->id }}" {{ old('supervisor_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} {{ $sup->lastname }}
                        </option>
                        @empty
                        <option value="">No hay supervisores disponibles</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Costo Estimado (S/)</label>
                    <input type="number" step="0.01" name="estimated_cost" value="{{ old('estimated_cost') }}" class="form-input" placeholder="Ej: 4500.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="planificado" {{ old('status', 'planificado') == 'planificado' ? 'selected' : '' }}>📋 Planificado</option>
                        <option value="proceso" {{ old('status') == 'proceso' ? 'selected' : '' }}>⏳ En Proceso</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Ingredientes / Receta</label>
                    <textarea name="ingredients" class="form-textarea" placeholder="Lista de ingredientes y cantidades...">{{ old('ingredients') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.production') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Crear Lote</button>
            </div>
        </form>
    </div>
</div>
@endsection
