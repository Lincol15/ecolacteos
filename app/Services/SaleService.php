<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Reglas de cabecera de una venta de planta (web y API).
     *
     * @var array<string, string>
     */
    public const RULES = [
        'client_name' => 'required|string|max:150',
        'client_dni_ruc' => 'nullable|string|max:20',
        'client_email' => 'nullable|email',
        'client_phone' => 'nullable|string|max:20',
        'client_address' => 'nullable|string',
        'sale_date' => 'required|date',
        'payment_status' => 'required|in:pendiente,pagado,parcial,anulado',
        'payment_method' => 'required|in:efectivo,transferencia,yape,plin,cheque,tarjeta',
        'sale_type' => 'required|in:mostrador,delivery,mayorista,exportacion',
        'discount' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ];

    /**
     * Registra la venta con IGV (18%), sus ítems y la salida de inventario de cada producto.
     *
     * @param  array<string, mixed>  $data  cabecera validada con self::RULES
     * @param  Collection<int, array{product_id: int|string, batch_id?: int|string|null, quantity: float|int|string, unit_price: float|int|string}>  $items
     */
    public function create(array $data, Collection $items, int $servedBy): Sale
    {
        return DB::transaction(function () use ($data, $items, $servedBy) {
            $subtotal = 0;
            foreach ($items as $i) {
                $subtotal += (float) $i['quantity'] * (float) $i['unit_price'];
            }

            $tax = round($subtotal * 0.18, 2);
            $discount = (float) ($data['discount'] ?? 0);

            $sale = Sale::create([
                ...$data,
                'invoice_number' => 'FV-'.Carbon::parse($data['sale_date'])->format('Ymd').'-'.str_pad((string) (Sale::count() + 1), 4, '0', STR_PAD_LEFT),
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => round($subtotal + $tax - $discount, 2),
                'served_by' => $servedBy,
            ]);

            foreach ($items as $i) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $i['product_id'],
                    'production_batch_id' => $i['batch_id'] ?? null,
                    'quantity' => $i['quantity'],
                    'unit_price' => $i['unit_price'],
                    'subtotal' => round((float) $i['quantity'] * (float) $i['unit_price'], 2),
                ]);

                $product = Product::find($i['product_id']);
                Inventory::create([
                    'production_batch_id' => $i['batch_id'] ?? null,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit' => $product?->unit,
                    'movement_type' => 'salida',
                    'unit_cost' => $product?->unit_price ? round(($product->unit_price * 0.65), 2) : 0,
                    'total_value' => $product?->unit_price ? round((float) $i['quantity'] * ($product->unit_price * 0.65), 2) : 0,
                    'location' => 'Punto de Venta',
                    'reference_document' => $sale->invoice_number,
                    'related_sale_id' => $sale->id,
                    'processed_by' => $servedBy,
                    'notes' => 'Venta #'.$sale->invoice_number,
                ]);
            }

            return $sale;
        });
    }
}
