<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    /**
     * Reglas de una receta (web y API).
     *
     * @var array<string, string>
     */
    public const RULES = [
        'product_id' => 'required|exists:products,id',
        'name' => 'required|string|max:150',
        'instructions' => 'nullable|string|max:2000',
        'ingredients' => 'required|array|min:1',
        'ingredients.*.ingredient_id' => 'required|distinct|exists:ingredients,id',
        'ingredients.*.quantity_per_unit' => 'required|numeric|min:0.001|max:100000',
    ];

    /**
     * @var array<string, string>
     */
    public const MESSAGES = [
        'ingredients.*.ingredient_id.distinct' => 'No repitas el mismo insumo en la receta.',
    ];

    /**
     * Crea la receta con sus insumos. La leche por unidad sale del insumo marcado como leche.
     *
     * @param  array{product_id: int|string, name: string, instructions?: ?string, ingredients: array<int, array{ingredient_id: int|string, quantity_per_unit: float|int|string}>}  $data
     */
    public function create(array $data, int $createdBy): Recipe
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $milkQty = collect($data['ingredients'])->first(function ($row) {
                return Ingredient::where('id', $row['ingredient_id'])->value('is_milk');
            });

            $recipe = Recipe::create([
                'product_id' => $data['product_id'],
                'name' => $data['name'],
                'instructions' => $data['instructions'] ?? null,
                'milk_liters_per_unit' => $milkQty['quantity_per_unit'] ?? 0,
                'active' => true,
                'created_by' => $createdBy,
            ]);

            foreach ($data['ingredients'] as $row) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $row['ingredient_id'],
                    'quantity_per_unit' => $row['quantity_per_unit'],
                ]);
            }

            return $recipe;
        });
    }
}
