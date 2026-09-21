@extends('layouts.app')

@section('page-title', 'Insumos')
@section('page-subtitle', 'Stock de leche e insumos de producción')

@section('content')
<div class="stats-grid">
    @foreach($ingredients as $ing)
    <div class="stat-card {{ $ing->is_milk ? 'blue' : 'green' }}">
        <div class="stat-icon-wrap {{ $ing->is_milk ? 'blue' : 'green' }}">{{ $ing->is_milk ? '🥛' : '🧂' }}</div>
        <div class="stat-label">{{ $ing->name }}</div>
        <div class="stat-value {{ $ing->is_milk ? 'blue' : 'green' }}">{{ number_format($ing->currentStock(), 2) }} {{ $ing->unit }}</div>
        <x-ingredient-stock-note :ingredient="$ing" milkLabel="Disponible para producción (registrada por el acopiador)" />
    </div>
    @endforeach
    <div class="stat-card cyan">
        <div class="stat-icon-wrap cyan">🚛</div>
        <div class="stat-label">Leche Recibida Hoy</div>
        <div class="stat-value" style="color:#0891b2">{{ number_format($milkReceivedToday, 2) }} L</div>
        <div style="font-size:11px; color:var(--text-light); margin-top:4px;">Entregas ya analizadas por Calidad hoy</div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">➕ Registrar Nuevo Insumo</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.ingredient-store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ej: Cuajo">
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad *</label>
                    <select name="unit" required class="form-select">
                        @foreach(\App\Models\Ingredient::UNITS as $value => $label)
                        <option value="{{ $value }}">{{ $label }} ({{ $value }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Stock Mínimo (alerta)</label>
                    <input type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock') }}" class="form-input">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Registrar Insumo</button>
            </div>
        </form>
    </div>
</div>

<div class="panel" style="margin-top:20px;">
    <div class="panel-header">
        <div class="panel-title">🛒 Registrar Compra de Insumos</div>
    </div>
    <div class="panel-body">
        <p class="form-hint" style="margin-top:-4px;">Agrega cada insumo comprado (ej. Sal, Cuajo, Cultivo) al carrito y regístralos todos juntos en una sola compra.</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Insumo *</label>
                <select id="cart_ingredient" class="form-select">
                    <option value="">-- Seleccionar --</option>
                    @foreach($ingredients->where('is_milk', false) as $ing)
                    <option value="{{ $ing->id }}" data-name="{{ $ing->name }}" data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Movimiento *</label>
                <select id="cart_movement_type" class="form-select">
                    <option value="entrada">📥 Entrada (compra)</option>
                    <option value="ajuste">⚙️ Ajuste</option>
                    <option value="merma">⚠️ Merma</option>
                    <option value="salida">📤 Salida manual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad *</label>
                <input type="number" step="0.01" min="0.01" id="cart_quantity" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label" id="cart_notes_label">Notas</label>
                <input type="text" id="cart_notes" class="form-input" placeholder="Ej: Compra a proveedor local">
            </div>
            <div class="form-group" style="grid-column: span 2; align-self:end;">
                <button type="button" id="cart_add_btn" class="btn btn-accent" style="width:100%;">➕ Agregar al carrito</button>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Insumo</th><th>Tipo</th><th>Cantidad</th><th>Notas</th><th></th></tr></thead>
                <tbody id="purchase_cart_body">
                    <tr id="purchase_cart_empty"><td colspan="5" class="empty"><div class="empty-icon">🛒</div>Carrito vacío. Agrega al menos un insumo.</td></tr>
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('admin.ingredient-adjust') }}" id="purchase-form" style="margin-top:14px;">
            @csrf
            <div id="purchase_cart_inputs"></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Registrar Compra(s)</button>
            </div>
        </form>
    </div>
</div>

<script>
let purchaseCart = [];
const MOVEMENT_LABELS = { entrada: '📥 Entrada', ajuste: '⚙️ Ajuste', merma: '⚠️ Merma', salida: '📤 Salida' };

document.getElementById('cart_movement_type')?.addEventListener('change', function () {
    const notesRequired = this.value === 'ajuste' || this.value === 'merma';
    document.getElementById('cart_notes_label').textContent = notesRequired ? 'Notas *' : 'Notas';
});

document.getElementById('cart_add_btn')?.addEventListener('click', function () {
    const ingSelect = document.getElementById('cart_ingredient');
    const ingredientId = ingSelect.value;
    const opt = ingSelect.options[ingSelect.selectedIndex];
    const movementType = document.getElementById('cart_movement_type').value;
    const quantity = parseFloat(document.getElementById('cart_quantity').value);
    const notesInput = document.getElementById('cart_notes');
    const notes = notesInput.value.trim();

    if (!ingredientId) { alert('Selecciona un insumo.'); return; }
    if (!quantity || quantity <= 0) { alert('Ingresa una cantidad válida.'); return; }
    if ((movementType === 'ajuste' || movementType === 'merma') && !notes) {
        alert('Debes indicar una observación para movimientos de ajuste o merma.');
        return;
    }

    purchaseCart.push({
        ingredient_id: ingredientId,
        ingredient_name: opt.dataset.name,
        unit: opt.dataset.unit,
        movement_type: movementType,
        quantity: quantity,
        notes: notes,
    });

    ingSelect.value = '';
    document.getElementById('cart_quantity').value = '';
    notesInput.value = '';
    renderPurchaseCart();
});

function removeFromPurchaseCart(index) {
    purchaseCart.splice(index, 1);
    renderPurchaseCart();
}

function renderPurchaseCart() {
    const body = document.getElementById('purchase_cart_body');
    body.innerHTML = '';

    if (purchaseCart.length === 0) {
        body.innerHTML = '<tr id="purchase_cart_empty"><td colspan="5" class="empty"><div class="empty-icon">🛒</div>Carrito vacío. Agrega al menos un insumo.</td></tr>';
    } else {
        purchaseCart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${item.ingredient_name}</strong></td>
                <td>${MOVEMENT_LABELS[item.movement_type] ?? item.movement_type}</td>
                <td>${item.quantity} ${item.unit}</td>
                <td style="font-size:12px;color:var(--text-light)">${item.notes || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-ghost" data-remove="${index}">🗑️</button></td>
            `;
            body.appendChild(row);
        });
    }

    body.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => removeFromPurchaseCart(parseInt(btn.dataset.remove, 10)));
    });
}

document.getElementById('purchase-form')?.addEventListener('submit', function (e) {
    if (purchaseCart.length === 0) {
        e.preventDefault();
        alert('Agrega al menos un insumo al carrito antes de registrar la compra.');
        return;
    }
    const container = document.getElementById('purchase_cart_inputs');
    container.innerHTML = '';
    purchaseCart.forEach((item, index) => {
        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${index}][ingredient_id]" value="${item.ingredient_id}">
            <input type="hidden" name="items[${index}][movement_type]" value="${item.movement_type}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${index}][notes]" value="${item.notes}">
        `);
    });
});
</script>
@endif

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">📋 Movimientos Recientes de Insumos</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Insumo</th><th>Tipo</th><th>Cantidad</th><th>Registrado por</th><th>Lote</th><th>Notas</th></tr></thead>
            <tbody>
                @forelse($ingredientMovements as $m)
                <tr>
                    <td style="font-size:12px;color:var(--text-light)">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->ingredient?->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ match($m->movement_type) { 'entrada' => 'badge-green', 'salida' => 'badge-red', 'merma' => 'badge-orange', default => 'badge-blue' } }}">
                            {{ \App\Models\IngredientMovement::MOVEMENT_TYPES[$m->movement_type] ?? $m->movement_type }}
                        </span>
                    </td>
                    <td style="font-weight:700">{{ number_format($m->quantity, 2) }} {{ $m->ingredient?->unit }}</td>
                    <td style="font-size:12px">{{ $m->processedBy?->fullname ?? '-' }}</td>
                    <td>{{ $m->productionBatch?->batch_number ?? '-' }}</td>
                    <td style="font-size:12px;color:var(--text-light)">{{ $m->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty"><div class="empty-icon">📋</div><h3>Sin movimientos registrados</h3></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
