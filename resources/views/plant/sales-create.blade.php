@extends('layouts.app')

@section('page-title', 'Nueva Venta')
@section('page-subtitle', 'Registra una nueva venta de productos terminados')

@section('top-actions')
    <a href="{{ route('plant.sales', ['tab' => 'historial']) }}" class="btn btn-ghost">← Volver a Ventas</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧾 Registrar Nueva Venta</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('plant.sales-store') }}" id="saleForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre Cliente *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required class="form-input" placeholder="Ej: Distribuidora ABC SAC">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI / RUC</label>
                    <input type="text" name="client_dni_ruc" value="{{ old('client_dni_ruc') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Cliente</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono Cliente</label>
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Venta *</label>
                    <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Venta *</label>
                    <select name="sale_type" required class="form-select">
                        <option value="mostrador" {{ old('sale_type', 'mostrador') === 'mostrador' ? 'selected' : '' }}>🏪 Mostrador</option>
                        <option value="delivery" {{ old('sale_type') === 'delivery' ? 'selected' : '' }}>🚚 Delivery</option>
                        <option value="mayorista" {{ old('sale_type') === 'mayorista' ? 'selected' : '' }}>📦 Mayorista</option>
                        <option value="exportacion" {{ old('sale_type') === 'exportacion' ? 'selected' : '' }}>🌍 Exportación</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Dirección Cliente</label>
                    <input type="text" name="client_address" value="{{ old('client_address') }}" class="form-input">
                </div>
            </div>

            <div style="margin-top:28px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:16px; font-weight:700;">📦 Productos / Líneas de Venta</h3>
                <button type="button" id="addLineBtn" class="btn btn-info btn-sm">➕ Agregar Producto</button>
            </div>

            <div id="linesContainer">
                <div class="sale-line" style="padding:18px; background:#f8fafc; border-radius:14px; border:1px solid var(--border); margin-bottom:14px;">
                    <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Producto *</label>
                            <select name="items[0][product_id]" required class="form-select product-select">
                                <option value="">Seleccionar...</option>
                                @forelse($products ?? [] as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->unit_price ?? 0 }}">
                                    {{ $product->name }} (S/{{ number_format($product->unit_price ?? 0, 2) }})
                                </option>
                                @empty
                                <option value="">No hay productos</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" step="0.01" min="1" name="items[0][quantity]" required value="1" class="form-input line-qty">
                        </div>
                        <div class="form-group">
                            <label class="form-label">P. Unit. *</label>
                            <input type="number" step="0.01" min="0" name="items[0][unit_price]" required class="form-input line-price">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-input line-subtotal" readonly style="background:#e2e8f0;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-line" style="height:42px;">✖</button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="max-width:420px; margin-left:auto; margin-top:24px; padding:20px; background:linear-gradient(135deg, #f0fdf4, #ecfeff); border-radius:16px; border:1px solid #6ee7b7;">
                <div class="form-group">
                    <label class="form-label">Subtotal</label>
                    <input type="text" id="subtotalDisplay" value="S/0.00" readonly class="form-input" style="font-weight:700; background:#fff;">
                </div>
                <div class="form-group">
                    <label class="form-label">Descuento (S/)</label>
                    <input type="number" step="0.01" min="0" name="discount" id="discountAmount" value="{{ old('discount', 0) }}" class="form-input">
                </div>
                <div class="form-group" style="margin-top:10px; padding-top:14px; border-top:2px dashed #10b981;">
                    <label class="form-label" style="font-size:13px;">IGV (18%) incluido en el total estimado</label>
                    <input type="text" id="totalDisplay" value="S/0.00" readonly class="form-input" style="font-size:26px; font-weight:800; color:#065f46; background:#fff;">
                </div>
            </div>

            <div class="form-grid" style="margin-top:24px;">
                <div class="form-group">
                    <label class="form-label">Método de Pago *</label>
                    <select name="payment_method" required class="form-select">
                        <option value="efectivo" {{ old('payment_method') == 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                        <option value="transferencia" {{ old('payment_method') == 'transferencia' ? 'selected' : '' }}>🏦 Transferencia</option>
                        <option value="yape" {{ old('payment_method') == 'yape' ? 'selected' : '' }}>📱 Yape</option>
                        <option value="plin" {{ old('payment_method') == 'plin' ? 'selected' : '' }}>📱 Plin</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>📄 Cheque</option>
                        <option value="tarjeta" {{ old('payment_method') == 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado Pago *</label>
                    <select name="payment_status" required class="form-select">
                        <option value="pagado" {{ old('payment_status') == 'pagado' ? 'selected' : '' }}>✅ Pagado</option>
                        <option value="pendiente" {{ old('payment_status') == 'pendiente' ? 'selected' : '' }}>❌ Pendiente</option>
                        <option value="parcial" {{ old('payment_status') == 'parcial' ? 'selected' : '' }}>⏳ Parcial</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label class="form-label">Notas / Observaciones</label>
                <textarea name="notes" class="form-textarea" placeholder="Observaciones internas o detalles de la venta...">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('plant.sales', ['tab' => 'historial']) }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Registrar Venta</button>
            </div>
        </form>
    </div>
</div>

<script>
let lineIndex = 1;
const addLineBtn = document.getElementById('addLineBtn');
const linesContainer = document.getElementById('linesContainer');
const productsOptions = document.querySelector('.product-select').innerHTML;

function recalcTotals() {
    let subtotal = 0;
    document.querySelectorAll('.sale-line').forEach(line => {
        const qty = parseFloat(line.querySelector('.line-qty').value) || 0;
        const price = parseFloat(line.querySelector('.line-price').value) || 0;
        const sub = qty * price;
        line.querySelector('.line-subtotal').value = 'S/' + sub.toFixed(2);
        subtotal += sub;
    });
    const discAmt = parseFloat(document.getElementById('discountAmount').value) || 0;
    const withDisc = Math.max(0, subtotal - discAmt);
    const total = withDisc * 1.18;

    document.getElementById('subtotalDisplay').value = 'S/' + subtotal.toFixed(2);
    document.getElementById('totalDisplay').value = 'S/' + total.toFixed(2);
}

addLineBtn.addEventListener('click', function() {
    const div = document.createElement('div');
    div.className = 'sale-line';
    div.style.cssText = 'padding:18px; background:#f8fafc; border-radius:14px; border:1px solid var(--border); margin-bottom:14px;';
    div.innerHTML = `
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:12px;">
            <div class="form-group">
                <label class="form-label">Producto *</label>
                <select name="items[${lineIndex}][product_id]" required class="form-select product-select">${productsOptions}</select>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad *</label>
                <input type="number" step="0.01" min="1" name="items[${lineIndex}][quantity]" required value="1" class="form-input line-qty">
            </div>
            <div class="form-group">
                <label class="form-label">P. Unit. *</label>
                <input type="number" step="0.01" min="0" name="items[${lineIndex}][unit_price]" required class="form-input line-price">
            </div>
            <div class="form-group">
                <label class="form-label">Subtotal</label>
                <input type="text" class="form-input line-subtotal" readonly style="background:#e2e8f0;">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-line" style="height:42px;">✖</button>
            </div>
        </div>`;
    linesContainer.appendChild(div);
    lineIndex++;
    bindLine(div);
    recalcTotals();
});

function bindLine(line) {
    line.querySelector('.remove-line').addEventListener('click', function() {
        if (document.querySelectorAll('.sale-line').length > 1) {
            line.remove();
            recalcTotals();
        }
    });
    line.querySelector('.product-select').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const price = opt.getAttribute('data-price') || 0;
        line.querySelector('.line-price').value = parseFloat(price).toFixed(2);
        recalcTotals();
    });
    line.querySelector('.line-qty').addEventListener('input', recalcTotals);
    line.querySelector('.line-price').addEventListener('input', recalcTotals);
}

document.querySelectorAll('.sale-line').forEach(bindLine);
document.getElementById('discountAmount').addEventListener('input', recalcTotals);
recalcTotals();
</script>
@endsection
