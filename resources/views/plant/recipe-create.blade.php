@extends('layouts.app')

@section('page-title', 'Nueva Receta')
@section('page-subtitle', 'Producto terminado + ingredientes necesarios por unidad')

@section('top-actions')
    <a href="{{ route('plant.production', ['tab' => 'recetas']) }}" class="btn btn-ghost">← Volver a Recetas</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📖 Datos de la Receta</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('plant.recipe-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Producto Terminado *</label>
                    <select name="product_id" required class="form-select">
                        <option value="">Seleccionar producto...</option>
                        @foreach($products ?? [] as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de la Receta *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Queso Fresco 1kg">
                </div>
            </div>

            <div style="margin-top:24px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:15px; font-weight:700;">🧂 Ingredientes por unidad de producto terminado</h3>
                <button type="button" id="addIngredientBtn" class="btn btn-info btn-sm">➕ Agregar Ingrediente</button>
            </div>

            <div id="ingredientsContainer">
                <div class="ingredient-line" style="padding:16px; background:#f8fafc; border-radius:12px; border:1px solid var(--border); margin-bottom:12px;">
                    <div class="form-grid" style="grid-template-columns: 2fr 1fr auto; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Insumo *</label>
                            <select name="ingredients[0][ingredient_id]" required class="form-select ingredient-select">
                                <option value="">Seleccionar...</option>
                                @forelse($ingredients ?? [] as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }}) {{ $ing->is_milk ? '🥛' : '' }}</option>
                                @empty
                                <option value="">No hay insumos registrados</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cantidad por unidad *</label>
                            <input type="number" step="0.001" min="0.001" name="ingredients[0][quantity_per_unit]" required class="form-input" placeholder="Ej: 5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-ingredient" style="height:42px;">✖</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label class="form-label">Instrucciones</label>
                <textarea name="instructions" class="form-textarea" placeholder="Pasos y observaciones de elaboración...">{{ old('instructions') }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('plant.production', ['tab' => 'recetas']) }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Receta</button>
            </div>
        </form>
    </div>
</div>

<script>
let ingIndex = 1;
const addBtn = document.getElementById('addIngredientBtn');
const container = document.getElementById('ingredientsContainer');
const ingredientOptions = document.querySelector('.ingredient-select').innerHTML;

function bindLine(line) {
    line.querySelector('.remove-ingredient').addEventListener('click', function () {
        if (document.querySelectorAll('.ingredient-line').length > 1) {
            line.remove();
        }
    });
}

addBtn.addEventListener('click', function () {
    const div = document.createElement('div');
    div.className = 'ingredient-line';
    div.style.cssText = 'padding:16px; background:#f8fafc; border-radius:12px; border:1px solid var(--border); margin-bottom:12px;';
    div.innerHTML = `
        <div class="form-grid" style="grid-template-columns: 2fr 1fr auto; gap:12px;">
            <div class="form-group">
                <label class="form-label">Insumo *</label>
                <select name="ingredients[${ingIndex}][ingredient_id]" required class="form-select ingredient-select">${ingredientOptions}</select>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad por unidad *</label>
                <input type="number" step="0.001" min="0.001" name="ingredients[${ingIndex}][quantity_per_unit]" required class="form-input" placeholder="Ej: 50">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-ingredient" style="height:42px;">✖</button>
            </div>
        </div>`;
    container.appendChild(div);
    ingIndex++;
    bindLine(div);
});

document.querySelectorAll('.ingredient-line').forEach(bindLine);
</script>
@endsection
