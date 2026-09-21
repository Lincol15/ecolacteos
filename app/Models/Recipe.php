<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'milk_liters_per_unit',
        'instructions',
        'active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'milk_liters_per_unit' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function nonMilkIngredients()
    {
        return $this->recipeIngredients()->whereHas('ingredient', fn ($q) => $q->where('is_milk', false));
    }
}
