@extends('layouts.app')

@section('page-title', 'Nueva Producción')
@section('page-subtitle', 'Elige qué vas a producir y cuánto: la receta se encarga del resto')

@section('top-actions')
    <a href="{{ route('plant.production', ['tab' => 'historial']) }}" class="btn btn-ghost">← Volver a Producción</a>
@endsection

@section('content')
<form method="POST" action="{{ route('plant.production-store') }}" id="production-form" class="prod-wrap">
    @csrf
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🏭 Datos del lote</div>
        </div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="production_date">Fecha de producción *</label>
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', date('Y-m-d')) }}" required class="form-input" max="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Estado *</label>
                    <select name="status" id="status" required class="form-select">
                        <option value="planeado" @selected(old('status') == 'planeado')>📋 Planeado</option>
                        <option value="en_proceso" @selected(old('status', 'en_proceso') == 'en_proceso')>⚙️ En proceso</option>
                        <option value="curando" @selected(old('status') == 'curando')>⏳ Curando</option>
                        <option value="terminado" @selected(old('status') == 'terminado')>✅ Terminado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🧀 Productos a elaborar</div>
        </div>
        <div class="panel-body">
            @if($products->isEmpty())
            <div class="empty">
                <div class="empty-icon">📖</div>
                <h3>No hay productos con receta</h3>
                <p>Para producir un producto primero crea su receta. <a href="{{ route('plant.recipe-create') }}" class="link">Crear receta</a></p>
            </div>
            @else
            <div class="prod-add">
                <div class="form-group">
                    <label class="form-label" for="cart_product">Producto</label>
                    <select id="cart_product" class="form-select">
                        <option value="">Seleccionar producto...</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" data-unit="{{ $p->unit }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="cart_qty">Cantidad (<span id="cart_unit">und</span>)</label>
                    <input type="number" step="0.01" min="0.01" id="cart_qty" class="form-input" placeholder="Ej: 10">
                </div>
                <div class="form-group">
                    <label class="form-label" for="cart_expiration">Vence (opcional)</label>
                    <input type="date" id="cart_expiration" class="form-input">
                </div>
                <div class="form-group" style="align-self:end;">
                    <button type="button" id="cart_add_btn" class="btn btn-accent" style="width:100%;">➕ Agregar</button>
                </div>
            </div>
            <p class="form-hint" style="margin-top:8px;">
                ¿No aparece tu producto? Aquí solo salen los productos que tienen receta.
                <a href="{{ route('plant.recipe-create') }}" class="link">Crear receta</a>
            </p>

            <div id="cart_list" class="prod-cart"></div>
            @endif

            <div id="shortage_alert" class="alert alert-error" style="display:none; margin-top:16px;"></div>

            <div id="cart_items_inputs"></div>
            <div class="form-actions">
                <a href="{{ route('plant.production', ['tab' => 'historial']) }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" id="submit_btn" class="btn btn-primary" disabled>💾 Crear lote(s)</button>
            </div>
        </div>
    </div>
</form>

<style>
    .prod-wrap { max-width: 900px; }
    .prod-add { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1.2fr) auto; gap: 10px; }
    .prod-add .form-group { margin: 0; }
    .prod-cart { display: grid; gap: 8px; margin-top: 16px; }
    .prod-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; border: 1px solid var(--border); border-radius: 12px; padding: 12px 16px; background: #fff; }
    .prod-line strong { font-size: 15px; }
    .prod-line-meta { font-size: 12.5px; color: var(--text-light); margin-top: 2px; }
    .prod-empty { text-align: center; padding: 24px 10px; color: var(--text-light); border: 1.5px dashed var(--border); border-radius: 12px; font-size: 13.5px; }
    @media (max-width: 640px) { .prod-add { grid-template-columns: 1fr 1fr; } .prod-add .form-group:first-child { grid-column: 1 / -1; } }
</style>

<script>
const RECIPES = @json($recipesForJs ?? []);
const INGREDIENT_STOCKS = @json($ingredientStocks ?? []);
const AVAILABLE_MILK_LITERS = {{ (float) collect($availableMilk)->sum('liters') }};

(function () {
    const cart = [];
    const fmt = (n) => Number.isInteger(n) ? String(n) : n.toFixed(2).replace(/\.?0+$/, '');
    const productSelect = document.getElementById('cart_product');
    const recipeFor = (id) => RECIPES.find((r) => String(r.product_id) === String(id)) || null;
    const stockFor = (id) => INGREDIENT_STOCKS.find((i) => String(i.id) === String(id))?.stock ?? 0;

    productSelect?.addEventListener('change', () => {
        document.getElementById('cart_unit').textContent = productSelect.selectedOptions[0]?.dataset.unit || 'und';
    });

    document.getElementById('cart_add_btn')?.addEventListener('click', () => {
        const qty = parseFloat(document.getElementById('cart_qty').value);
        if (!productSelect.value) { alert('Selecciona un producto.'); return; }
        if (!qty || qty <= 0) { alert('Ingresa una cantidad válida.'); return; }
        const option = productSelect.selectedOptions[0];
        cart.push({
            product_id: productSelect.value,
            name: option.textContent.trim(),
            unit: option.dataset.unit || 'und',
            qty,
            expiration: document.getElementById('cart_expiration').value || '',
            recipe: recipeFor(productSelect.value),
        });
        productSelect.value = '';
        document.getElementById('cart_qty').value = '';
        document.getElementById('cart_expiration').value = '';
        render();
    });

    // Solo avisa si algo no alcanza; el detalle de lo que se usa lo resuelve la receta.
    function shortages() {
        const needs = {};
        let milk = 0;
        cart.forEach((item) => {
            milk += item.recipe.milk_liters_per_unit * item.qty;
            item.recipe.ingredients.forEach((ing) => {
                needs[ing.id] ??= { name: ing.name, needed: 0 };
                needs[ing.id].needed += ing.qty * item.qty;
            });
        });
        const missing = [];
        if (milk > AVAILABLE_MILK_LITERS) { missing.push('leche'); }
        Object.entries(needs).forEach(([id, n]) => { if (n.needed > stockFor(id)) { missing.push(n.name.toLowerCase()); } });
        return missing;
    }

    function render() {
        const list = document.getElementById('cart_list');
        if (list) {
            list.innerHTML = cart.length ? '' : '<div class="prod-empty">Aún no agregaste productos. Elige un producto y la cantidad a producir.</div>';
            cart.forEach((item, index) => {
                list.insertAdjacentHTML('beforeend', `
                    <div class="prod-line">
                        <div>
                            <strong>${item.name}</strong>
                            <div class="prod-line-meta">${fmt(item.qty)} ${item.unit}${item.expiration ? ' · vence ' + item.expiration.split('-').reverse().join('/') : ''}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-ghost" data-remove="${index}" aria-label="Quitar">🗑️</button>
                    </div>`);
            });
            list.querySelectorAll('[data-remove]').forEach((btn) => btn.addEventListener('click', () => {
                cart.splice(parseInt(btn.dataset.remove, 10), 1);
                render();
            }));
        }

        const missing = cart.length ? shortages() : [];
        const alertBox = document.getElementById('shortage_alert');
        alertBox.style.display = missing.length ? '' : 'none';
        alertBox.textContent = missing.length ? `❌ No alcanza para esta producción: falta ${missing.join(', ')}. Reduce la cantidad o pide reponer el stock.` : '';
        document.getElementById('submit_btn').disabled = !cart.length || missing.length > 0;
    }

    document.getElementById('production-form').addEventListener('submit', (e) => {
        if (!cart.length) { e.preventDefault(); return; }
        const container = document.getElementById('cart_items_inputs');
        container.innerHTML = '';
        cart.forEach((item, index) => {
            container.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                <input type="hidden" name="items[${index}][output_units]" value="${item.qty}">
                <input type="hidden" name="items[${index}][expiration_date]" value="${item.expiration}">`);
        });
    });

    render();
})();
</script>
@endsection
