@extends('layouts.app')

@section('page-title', 'Editar Producto')
@section('page-subtitle', $product->name)

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧀 Datos del Producto</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.product-update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="category" required class="form-select">
                        @foreach(\App\Models\Product::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $product->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio de Venta (S/) *</label>
                    <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $product->unit_price) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad *</label>
                    <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Emoji (si no hay foto)</label>
                    <input type="text" name="emoji" value="{{ old('emoji', $product->emoji) }}" class="form-input" maxlength="20">
                </div>
                <div class="form-group">
                    <label class="form-label">Foto del Producto</label>
                    @if($product->image_url)
                        <div style="width:60px;height:60px;border-radius:10px;background-size:cover;background-position:center;background-image:url('{{ $product->image_url }}');margin-bottom:8px;"></div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="form-input">
                    <p class="form-hint">Deja vacío para conservar la foto actual.</p>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción Corta</label>
                    <input type="text" name="description" value="{{ old('description', $product->description) }}" class="form-input" maxlength="500">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Descripción Detallada</label>
                    <textarea name="long_description" class="form-textarea">{{ old('long_description', $product->long_description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Visible en Catálogo *</label>
                    <select name="show_in_catalog" required class="form-select">
                        <option value="1" {{ old('show_in_catalog', $product->show_in_catalog ? '1' : '0') == '1' ? 'selected' : '' }}>✅ Sí</option>
                        <option value="0" {{ old('show_in_catalog', $product->show_in_catalog ? '1' : '0') == '0' ? 'selected' : '' }}>🚫 No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="is_active" required class="form-select">
                        <option value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="0" {{ old('is_active', $product->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>❌ Inactivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Orden de Aparición</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}" class="form-input">
                </div>
            </div>

            <div style="margin-top:20px;">
                <label class="form-label">Especificaciones (opcional)</label>
                <p class="form-hint" style="margin-top:-4px;">Ej: "Presentación" → "500g", "Origen" → "Comunidad Huata"</p>
                <div id="specs_rows">
                    @foreach($product->specifications ?? [] as $key => $value)
                    <div style="display:flex;gap:10px;margin-bottom:8px;align-items:center;">
                        <input type="text" name="spec_key[]" class="form-input" value="{{ $key }}" style="flex:1;">
                        <input type="text" name="spec_value[]" class="form-input" value="{{ $value }}" style="flex:1;">
                        <button type="button" class="btn btn-ghost btn-sm" data-remove-spec>🗑️</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="add_spec_btn" class="btn btn-ghost btn-sm">➕ Agregar Especificación</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                <a href="{{ route('admin.products') }}" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[data-remove-spec]').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('div').remove());
});
document.getElementById('add_spec_btn')?.addEventListener('click', function () {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:10px;margin-bottom:8px;align-items:center;';
    row.innerHTML = `
        <input type="text" name="spec_key[]" class="form-input" placeholder="Clave (Ej: Presentación)" style="flex:1;">
        <input type="text" name="spec_value[]" class="form-input" placeholder="Valor (Ej: 500g)" style="flex:1;">
        <button type="button" class="btn btn-ghost btn-sm" data-remove-spec>🗑️</button>
    `;
    row.querySelector('[data-remove-spec]').addEventListener('click', () => row.remove());
    document.getElementById('specs_rows').appendChild(row);
});
</script>
@endsection
