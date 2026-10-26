<?php

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Sale
 */
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'client_name' => $this->client_name,
            'sale_date' => $this->sale_date ? substr((string) $this->sale_date, 0, 10) : null,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'total_amount' => (float) $this->total_amount,
            'payment_status' => $this->payment_status,
            'served_by' => $this->served_by,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'product_category' => $item->product?->category,
                'unit' => $item->product?->unit,
                'production_batch_id' => $item->production_batch_id,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ])->values()),
        ];
    }
}
