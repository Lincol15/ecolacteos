@extends('layouts.app')

@section('page-title', 'Nueva Producción')
@section('page-subtitle', 'Agrega uno o varios productos al carrito y crea sus lotes de producción')

@section('top-actions')
    <a href="{{ route('plant.production', ['tab' => 'historial']) }}" class="btn btn-ghost">← Volver a Producción</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🏭 Nuevo Lote de Producción</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('plant.production-store') }}" id="production-form">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Fecha de Producción *</label>
                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', date('Y-m-d')) }}" required class="form-input" max="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="status" required class="form-select">
                        <option value="planeado" {{ old('status') == 'planeado' ? 'selected' : '' }}>📋 Planeado</option>
                        <option value="en_proceso" {{ old('status', 'en_proceso') == 'en_proceso' ? 'selected' : '' }}>⚙️ En Proceso</option>
                        <option value="curando" {{ old('status') == 'curando' ? 'selected' : '' }}>⏳ Curando</option>
                        <option value="terminado" {{ old('status') == 'terminado' ? 'selected' : '' }}>✅ Terminado</option>
                    </select>
                </div>
            </div>

            <div class="panel" style="margin:20px 0; background:#f8fafc;">
                <div class="panel-header">
                    <div class="panel-title">🛒 Carrito de Producción</div>
                </div>
                <div class="panel-body">
                    <p class="form-hint" style="margin-top:-4px;">Agrega cada producto que vas a elaborar (ej. Queso y Yogurt). La leche se reparte entre todos los productos del carrito y cada uno descuenta sus propios insumos.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Producto</label>
                            <select id="cart_product" class="form-select">
                                <option value="">Seleccionar producto...</option>
                                @foreach($products ?? [] as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unidades a Producir</label>
                            <input type="number" step="0.01" min="0.01" id="cart_qty" class="form-input" placeholder="Ej: 20">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Vencimiento</label>
                            <input type="date" id="cart_expiration" class="form-input">
                        </div>
                        <div class="form-group" style="align-self:end;">
                            <button type="button" id="cart_add_btn" class="btn btn-accent" style="width:100%;">➕ Agregar al carrito</button>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Producto</th><th>Cantidad</th><th>Leche estimada</th><th>Insumos estimados</th><th>Vence</th><th></th></tr></thead>
                            <tbody id="cart_items_body">
                                <tr id="cart_empty_row"><td colspan="6" class="empty"><div class="empty-icon">🛒</div>Carrito vacío. Agrega al menos un producto.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="cart_summary" style="margin-top:14px; font-size:13px; display:flex; flex-wrap:wrap; gap:8px;"></div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Entregas de Leche a Usar *</label>
                    <select name="milk_ids[]" id="milk_ids" multiple required class="form-select" style="min-height:110px;">
                        @foreach($deliveries ?? [] as $d)
                        <option value="{{ $d->id }}" data-liters="{{ $d->liters }}">#{{ $d->id }} - {{ $d->producer?->user?->fullname ?? 'N/A' }} ({{ number_format($d->liters, 2) }} L, {{ $d->delivery_date?->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                    <small class="form-hint">Seleccione entregas registradas por el acopiador (no rechazadas) hasta cubrir la leche estimada del carrito. Se reparte proporcionalmente entre los productos que la necesitan.</small>
                    <small class="form-hint">Leche seleccionada: <strong id="milk_selected_total">0.00</strong> L — Leche estimada por el carrito: <strong id="milk_needed_total">0.00</strong> L</small>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Notas de Receta</label>
                    <textarea name="recipe_notes" class="form-textarea">{{ old('recipe_notes') }}</textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Notas de Calidad</label>
                    <textarea name="quality_notes" class="form-textarea">{{ old('quality_notes') }}</textarea>
                </div>
            </div>

            <div id="cart_items_inputs"></div>

            <div class="form-actions">
                <a href="{{ route('plant.production', ['tab' => 'historial']) }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Crear Lote(s)</button>
            </div>
        </form>
    </div>
</div>

<script>
const RECIPES = @json($recipesForJs ?? []);
const INGREDIENT_STOCKS = @json($ingredientStocks ?? []);

let cart = [];

function recipeFor(productId) {
    return RECIPES.find(r => String(r.product_id) === String(productId)) || null;
}

function productName(productId) {
    const opt = document.querySelector(`#cart_product option[value="${productId}"]`);
    return opt ? opt.textContent : `Producto #${productId}`;
}

function stockFor(ingredientId) {
    const s = INGREDIENT_STOCKS.find(i => String(i.id) === String(ingredientId));
    return s ? s.stock : null;
}

document.getElementById('cart_add_btn')?.addEventListener('click', function () {
    const productSelect = document.getElementById('cart_product');
    const productId = productSelect.value;
    const qty = parseFloat(document.getElementById('cart_qty').value);
    const expiration = document.getElementById('cart_expiration').value || null;

    if (!productId) { alert('Selecciona un producto.'); return; }
    if (!qty || qty <= 0) { alert('Ingresa una cantidad válida a producir.'); return; }

    const recipe = recipeFor(productId);
    cart.push({
        product_id: productId,
        product_name: productName(productId),
        output_units: qty,
        expiration_date: expiration,
        recipe: recipe,
    });

    productSelect.value = '';
    document.getElementById('cart_qty').value = '';
    document.getElementById('cart_expiration').value = '';
    renderCart();
});

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function aggregateIngredientNeeds() {
    const needs = {};
    cart.forEach(item => {
        if (!item.recipe) return;
        item.recipe.ingredients.forEach(ing => {
            const needed = ing.qty * item.output_units;
            if (!needs[ing.id]) needs[ing.id] = { name: ing.name, unit: ing.unit, needed: 0 };
            needs[ing.id].needed += needed;
        });
    });
    return needs;
}

function totalMilkNeeded() {
    return cart.reduce((sum, item) => sum + (item.recipe ? item.recipe.milk_liters_per_unit * item.output_units : 0), 0);
}

function renderCart() {
    const body = document.getElementById('cart_items_body');
    body.innerHTML = '';

    if (cart.length === 0) {
        body.innerHTML = '<tr id="cart_empty_row"><td colspan="6" class="empty"><div class="empty-icon">🛒</div>Carrito vacío. Agrega al menos un producto.</td></tr>';
    } else {
        cart.forEach((item, index) => {
            const milk = item.recipe ? (item.recipe.milk_liters_per_unit * item.output_units).toFixed(2) + ' L' : '—';
            const ingredientsText = item.recipe && item.recipe.ingredients.length
                ? item.recipe.ingredients.map(ing => `${ing.name}: ${(ing.qty * item.output_units).toFixed(2)} ${ing.unit}`).join(', ')
                : (item.recipe ? 'Sin insumos adicionales' : 'Sin receta activa');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${item.product_name}</strong></td>
                <td>${item.output_units}</td>
                <td>${milk}</td>
                <td style="font-size:12px;color:var(--text-light)">${ingredientsText}</td>
                <td>${item.expiration_date ?? '—'}</td>
                <td><button type="button" class="btn btn-sm btn-ghost" data-remove="${index}">🗑️</button></td>
            `;
            body.appendChild(row);
        });
    }

    body.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => removeFromCart(parseInt(btn.dataset.remove, 10)));
    });

    const summary = document.getElementById('cart_summary');
    summary.innerHTML = '';
    const needs = aggregateIngredientNeeds();
    Object.entries(needs).forEach(([id, n]) => {
        const stock = stockFor(id);
        const short = stock !== null && n.needed > stock;
        const span = document.createElement('span');
        span.className = 'badge ' + (short ? 'badge-red' : 'badge-gray');
        span.textContent = `${n.name}: necesita ${n.needed.toFixed(2)} ${n.unit}` + (stock !== null ? ` (stock: ${stock.toFixed(2)} ${n.unit})` : '');
        summary.appendChild(span);
    });

    document.getElementById('milk_needed_total').textContent = totalMilkNeeded().toFixed(2);
    updateMilkSelectedDisplay();
}

function updateMilkSelectedDisplay() {
    const select = document.getElementById('milk_ids');
    let total = 0;
    for (const opt of select.selectedOptions) {
        total += parseFloat(opt.dataset.liters) || 0;
    }
    document.getElementById('milk_selected_total').textContent = total.toFixed(2);
}

document.getElementById('milk_ids')?.addEventListener('change', updateMilkSelectedDisplay);

document.getElementById('production-form')?.addEventListener('submit', function (e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Agrega al menos un producto al carrito antes de crear el lote.');
        return;
    }
    const container = document.getElementById('cart_items_inputs');
    container.innerHTML = '';
    cart.forEach((item, index) => {
        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
            <input type="hidden" name="items[${index}][output_units]" value="${item.output_units}">
            <input type="hidden" name="items[${index}][expiration_date]" value="${item.expiration_date ?? ''}">
        `);
    });
});

renderCart();
</script>
@endsection
