<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_batch_id',
        'product_id',
        'quantity',
        'unit',
        'movement_type',
        'unit_cost',
        'total_value',
        'location',
        'expiration_date',
        'reference_document',
        'related_sale_id',
        'processed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_value' => 'decimal:2',
            'expiration_date' => 'date',
        ];
    }

    public const MOVEMENT_TYPES = [
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'ajuste' => 'Ajuste',
        'devolucion' => 'Devolución',
        'merma' => 'Merma',
    ];

    public function productionBatch()
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function relatedSale()
    {
        return $this->belongsTo(Sale::class, 'related_sale_id');
    }
}
