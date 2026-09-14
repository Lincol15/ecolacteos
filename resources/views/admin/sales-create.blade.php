@extends('layouts.app')

@section('page-title', 'Nueva Venta')
@section('page-subtitle', 'Registra una nueva factura o orden de venta')

@section('top-actions')
    <a href="{{ route('admin.sales') }}" class="btn btn-ghost">
        ← Volver a Ventas
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧾 Registrar Nueva Venta</div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.sales-store') }}" id="saleForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">N° Documento *</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', 'FAC-'.date('Ymd').'-'.rand(1000,9999)) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Venta *</label>
                    <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo Documento *</label>
                    <select name="sale_type" required class="form-select">
                        <option value="factura" {{ old('sale_type') == 'factura' ? 'selected' : '' }}>🧾 Factura</option>
                        <option value="boleta" {{ old('sale_type') == 'boleta' ? 'selected' : '' }}>📄 Boleta</option>
                        <option value="nota_venta" {{ old('sale_type') == 'nota_venta' ? 'selected' : '' }}>📝 Nota Venta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre Cliente *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required class="form-input" placeholder="Ej: Distribuidora ABC SAC">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Cliente</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono Cliente</label>
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}" class="form-input">
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
                            <select name="products[0][product_id]" required class="form-select product-select">
                                <option value="">Seleccionar...</option>
                                @forelse($products ?? [] as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price ?? 0 }}">
                                    {{ $product->name }} (S/{{ number_format($product->price ?? 0, 2) }})
                                </option>
                                @empty
                                <option value="">No hay productos</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" step="0.01" min="1" name="products[0][quantity]" required value="1" class="form-input line-qty">
                        </div>
                        <div class="form-group">
                            <label class="form-label">P. Unit. *</label>
                            <input type="number" step="0.01" min="0" name="products[0][price]" required class="form-input line-price">
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
                    <input type="hidden" name="subtotal" id="subtotalInput" value="0">
                </div>
                <div class="form-grid" style="grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Impuesto (%)</label>
                        <input type="number" step="0.01" min="0" name="tax_percent" id="taxPercent" value="18" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" step="0.01" min="0" name="discount_percent" id="discountPercent" value="0" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descuento Monto (S/)</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountAmount" value="0" class="form-input">
                </div>
                <div class="form-group" style="margin-top:10px; padding-top:14px; border-top:2px dashed #10b981;">
                    <label class="form-label" style="font-size:16px;">💵 TOTAL FINAL</label>
                    <input type="text" id="totalDisplay" value="S/0.00" readonly class="form-input" style="font-size:26px; font-weight:800; color:#065f46; background:#fff;">
                    <input type="hidden" name="total_amount" id="totalInput" value="0">
                </div>
            </div>

            <div class="form-grid" style="margin-top:24px;">
                <div class="form-group">
                    <label class="form-label">Método de Pago *</label>
                    <select name="payment_method" required class="form-select">
                        <option value="efectivo" {{ old('payment_method') == 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                        <option value="transferencia" {{ old('payment_method') == 'transferencia' ? 'selected' : '' }}>🏦 Transferencia</option>
                        <option value="tarjeta" {{ old('payment_method') == 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta</option>
                        <option value="credito" {{ old('payment_method') == 'credito' ? 'selected' : '' }}>📋 Crédito</option>
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
                <a href="{{ route('admin.sales') }}" class="btn btn-ghost">Cancelar</a>
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
    const taxPct = parseFloat(document.getElementById('taxPercent').value) || 0;
    const discPct = parseFloat(document.getElementById('discountPercent').value) || 0;
    const discAmt = parseFloat(document.getElementById('discountAmount').value) || 0;

    const discPercentAmt = subtotal * (discPct / 100);
    const totalDisc = discPercentAmt + discAmt;
    const withDisc = Math.max(0, subtotal - totalDisc);
    const tax = withDisc * (taxPct / 100);
    const total = withDisc + tax;

    document.getElementById('subtotalDisplay').value = 'S/' + subtotal.toFixed(2);
    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('totalDisplay').value = 'S/' + total.toFixed(2);
    document.getElementById('totalInput').value = total.toFixed(2);
}

addLineBtn.addEventListener('click', function() {
    const div = document.createElement('div');
    div.className = 'sale-line';
    div.style.cssText = 'padding:18px; background:#f8fafc; border-radius:14px; border:1px solid var(--border); margin-bottom:14px;';
    div.innerHTML = `
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:12px;">
            <div class="form-group">
                <label class="form-label">Producto *</label>
                <select name="products[${lineIndex}][product_id]" required class="form-select product-select">${productsOptions}</select>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad *</label>
                <input type="number" step="0.01" min="1" name="products[${lineIndex}][quantity]" required value="1" class="form-input line-qty">
            </div>
            <div class="form-group">
                <label class="form-label">P. Unit. *</label>
                <input type="number" step="0.01" min="0" name="products[${lineIndex}][price]" required class="form-input line-price">
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
document.getElementById('taxPercent').addEventListener('input', recalcTotals);
document.getElementById('discountPercent').addEventListener('input', recalcTotals);
document.getElementById('discountAmount').addEventListener('input', recalcTotals);
recalcTotals();
</script>
@endsection
