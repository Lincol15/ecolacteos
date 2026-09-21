<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientMovement extends Model
{
    protected $fillable = [
        'ingredient_id',
        'movement_type',
        'quantity',
        'production_batch_id',
        'processed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public const MOVEMENT_TYPES = [
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'ajuste' => 'Ajuste',
        'merma' => 'Merma',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function productionBatch()
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
