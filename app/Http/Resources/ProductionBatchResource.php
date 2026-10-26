<?php

namespace App\Http\Resources;

use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductionBatch
 */
class ProductionBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'group_number' => $this->group_number,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'product_category' => $this->whenLoaded('product', fn () => $this->product?->category),
            'unit' => $this->whenLoaded('product', fn () => $this->product?->unit),
            'input_milk_liters' => (float) $this->input_milk_liters,
            'output_units' => (float) $this->output_units,
            'stock' => round($this->currentStock(), 2),
            'production_date' => $this->production_date ? substr((string) $this->production_date, 0, 10) : null,
            'expiration_date' => $this->expiration_date ? substr((string) $this->expiration_date, 0, 10) : null,
            'status' => $this->status,
            'recipe_notes' => $this->recipe_notes,
        ];
    }
}
