@extends('layouts.app')

@section('page-title', 'Nueva Receta')
@section('page-subtitle', 'Define qué ingredientes lleva 1 unidad del producto terminado')

@section('top-actions')
    <a href="{{ route('plant.production', ['tab' => 'recetas']) }}" class="btn btn-ghost">← Volver a Recetas</a>
@endsection

@section('content')
<form method="POST" action="{{ route('plant.recipe-store') }}" id="recipeForm">
    @csrf
    <div class="grid-2" style="align-items:start">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📖 Datos de la receta</div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label" for="product_id">Producto terminado *</label>
                    <select name="product_id" id="product_id" required class="form-select">
                        <option value="">Seleccionar producto...</option>
                        @foreach($products ?? [] as $p)
                        <option value="{{ $p->id }}" data-unit="{{ $p->unit }}" @selected(old('product_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-hint">¿Falta un producto? Los productos nuevos los registra el administrador en Productos.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad de producción</label>
                    <div class="recipe-unit-box"><strong>1</strong> <span id="unitLabel">unidad</span></div>
                    <small class="form-hint">Las cantidades de abajo son para producir 1 unidad del producto.</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">Nombre de la receta *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Queso Andino Huata 1 kg">
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin:22px 0 10px;">
                    <h3 style="font-size:15px;">🧂 Ingredientes</h3>
                    <button type="button" id="addIngredientBtn" class="btn btn-info btn-sm">➕ Agregar ingrediente</button>
                </div>
                <div id="ingredientsContainer"></div>

                <div class="form-group" style="margin-top:18px;">
                    <label class="form-label" for="instructions">Instrucciones (opcional)</label>
                    <textarea name="instructions" id="instructions" class="form-textarea" rows="3" placeholder="Pasos de elaboración...">{{ old('instructions') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel" style="position:sticky; top:16px;">
            <div class="panel-header">
                <div class="panel-title">👀 Vista previa</div>
            </div>
            <div class="panel-body">
                <div class="preview-block">
                    <div class="preview-label">Producto terminado</div>
                    <div class="preview-value" id="previewProduct">—</div>
                </div>
                <div class="preview-block">
                    <div class="preview-label">Unidad de producción</div>
                    <div class="preview-value"><span class="preview-chip">1</span> <span class="preview-chip" id="previewUnit">unidad</span></div>
                </div>
                <div class="preview-block">
                    <div class="preview-label">Ingredientes</div>
                    <div id="previewIngredients" class="preview-list"></div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('plant.production', ['tab' => 'recetas']) }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">💾 Guardar receta</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="ingredientTemplate">
    <div class="ingredient-line">
        <select class="form-select ingredient-select" required>
            <option value="">Seleccionar insumo...</option>
            @foreach($ingredients ?? [] as $ing)
            <option value="{{ $ing->id }}" data-name="{{ $ing->name }}" data-unit="{{ $ing->unit }}" data-milk="{{ $ing->is_milk ? 1 : 0 }}">{{ $ing->is_milk ? '🥛' : '🧂' }} {{ $ing->name }} ({{ $ing->unit }})</option>
            @endforeach
        </select>
        <div class="qty-wrap">
            <input type="number" step="0.001" min="0.001" class="form-input ingredient-qty" required placeholder="Cantidad">
            <span class="ingredient-unit">—</span>
        </div>
        <button type="button" class="btn btn-sm btn-ghost remove-ingredient" title="Quitar" aria-label="Quitar ingrediente">✖</button>
    </div>
</template>

<style>
    .recipe-unit-box { padding: 10px 14px; border-radius: 10px; background: #F6FBF8; border: 1px solid var(--border); font-size: 15px; }
    .ingredient-line { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr) auto; gap: 8px; align-items: center; margin-bottom: 8px; }
    .qty-wrap { display: flex; align-items: center; gap: 6px; }
    .qty-wrap .form-input { min-width: 0; }
    .ingredient-unit { min-width: 26px; font-weight: 600; color: var(--text-light); }
    .preview-block { margin-bottom: 14px; }
    .preview-label { font-size: 11.5px; text-transform: uppercase; letter-spacing: .6px; color: var(--text-light); font-weight: 600; margin-bottom: 6px; }
    .preview-value { font-size: 16px; font-weight: 700; }
    .preview-chip { display: inline-block; padding: 3px 10px; border: 1px solid var(--border); border-radius: 8px; background: #fff; font-size: 14px; }
    .preview-list { display: grid; gap: 6px; }
    .preview-row { display: flex; justify-content: space-between; gap: 10px; font-size: 14px; padding: 6px 10px; border-radius: 8px; background: #F6FBF8; }
    .preview-row strong { white-space: nowrap; }
    .preview-empty { font-size: 13px; color: var(--text-light); }
    @media (max-width: 640px) { .ingredient-line { grid-template-columns: 1fr auto; } .ingredient-line select { grid-column: 1 / -1; } }
</style>

<script>
(function () {
    const container = document.getElementById('ingredientsContainer');
    const template = document.getElementById('ingredientTemplate');
    const product = document.getElementById('product_id');
    const fmt = (n) => Number.isInteger(n) ? String(n) : n.toFixed(3).replace(/\.?0+$/, '');
    let index = 0;

    const unitOfProduct = () => product.selectedOptions[0]?.dataset.unit || 'unidad';

    const lines = () => Array.from(container.querySelectorAll('.ingredient-line')).map((line) => {
        const option = line.querySelector('select').selectedOptions[0];
        return {
            name: option?.dataset.name,
            unit: option?.dataset.unit,
            milk: option?.dataset.milk === '1',
            qty: parseFloat(line.querySelector('.ingredient-qty').value) || 0,
        };
    }).filter((l) => l.name);

    const render = () => {
        const unit = unitOfProduct();
        document.getElementById('unitLabel').textContent = unit;
        document.getElementById('previewUnit').textContent = unit;
        document.getElementById('previewProduct').textContent = product.value ? product.selectedOptions[0].textContent : '—';

        const items = lines();
        const list = document.getElementById('previewIngredients');
        list.innerHTML = '';
        if (!items.length) {
            list.innerHTML = '<div class="preview-empty">Agrega ingredientes para verlos aquí.</div>';
            return;
        }
        items.forEach((item) => {
            list.insertAdjacentHTML('beforeend', `<div class="preview-row"><span>${item.milk ? '🥛' : '🧂'} ${item.name}</span><strong>${fmt(item.qty)} ${item.unit}</strong></div>`);
        });
    };

    const addLine = (ingredientId = '', quantity = '') => {
        const node = template.content.firstElementChild.cloneNode(true);
        const select = node.querySelector('select');
        const input = node.querySelector('.ingredient-qty');
        select.name = `ingredients[${index}][ingredient_id]`;
        input.name = `ingredients[${index}][quantity_per_unit]`;
        select.value = ingredientId;
        input.value = quantity;
        index++;

        const syncUnit = () => { node.querySelector('.ingredient-unit').textContent = select.selectedOptions[0]?.dataset.unit || '—'; };
        select.addEventListener('change', () => { syncUnit(); render(); });
        input.addEventListener('input', render);
        node.querySelector('.remove-ingredient').addEventListener('click', () => {
            if (container.children.length > 1) { node.remove(); render(); }
        });
        container.appendChild(node);
        syncUnit();
    };

    document.getElementById('addIngredientBtn').addEventListener('click', () => { addLine(); render(); });
    product.addEventListener('change', render);

    const oldIngredients = @json(array_values(old('ingredients', [])));
    if (oldIngredients.length) {
        oldIngredients.forEach((row) => addLine(row.ingredient_id ?? '', row.quantity_per_unit ?? ''));
    } else {
        addLine();
    }
    render();
})();
</script>
@endsection
