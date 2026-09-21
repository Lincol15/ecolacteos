<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'is_milk',
        'min_stock',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'is_milk' => 'boolean',
            'min_stock' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public const UNITS = [
        'L' => 'Litros',
        'ml' => 'Mililitros',
        'kg' => 'Kilogramos',
        'g' => 'Gramos',
        'und' => 'Unidades',
    ];

    public function movements()
    {
        return $this->hasMany(IngredientMovement::class);
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Stock disponible del insumo. Para la Leche, el stock no se ajusta
     * manualmente: es la suma de las entregas que el acopiador registró
     * (no rechazadas) que todavía no fueron usadas en un lote de producción.
     */
    public function currentStock(): float
    {
        if ($this->is_milk) {
            return (float) MilkDelivery::where('status', '!=', 'rechazado')
                ->whereDoesntHave('batches')
                ->sum('liters');
        }

        $in = $this->movements()->whereIn('movement_type', ['entrada', 'ajuste'])->sum('quantity');
        $out = $this->movements()->whereIn('movement_type', ['salida', 'merma'])->sum('quantity');

        return round((float) $in - (float) $out, 2);
    }
}
