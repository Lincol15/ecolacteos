<?php

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Recipe
 */
class RecipeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'product_category' => $this->whenLoaded('product', fn () => $this->product?->category),
            'milk_liters_per_unit' => (float) $this->milk_liters_per_unit,
            'instructions' => $this->instructions,
            'active' => (bool) $this->active,
            'ingredients' => $this->whenLoaded('recipeIngredients', fn () => $this->recipeIngredients->map(fn ($ri) => [
                'ingredient_id' => $ri->ingredient_id,
                'name' => $ri->ingredient?->name,
                'unit' => $ri->ingredient?->unit,
                'quantity_per_unit' => (float) $ri->quantity_per_unit,
            ])->values()),
        ];
    }
}
