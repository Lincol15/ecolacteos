<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'category',
        'description',
        'long_description',
        'unit_price',
        'unit',
        'image_url',
        'emoji',
        'specifications',
        'is_active',
        'show_in_catalog',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'specifications' => 'array',
            'is_active' => 'boolean',
            'show_in_catalog' => 'boolean',
        ];
    }

    public const CATEGORIES = [
        'queso' => 'Queso',
        'yogur' => 'Yogur',
        'mantequilla' => 'Mantequilla',
        'leche' => 'Leche',
        'otro' => 'Otro',
    ];

    public function productionBatches()
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function currentStock(): float
    {
        return (float) $this->inventories()
            ->selectRaw('SUM(CASE WHEN movement_type IN ("entrada", "devolucion") THEN quantity ELSE -quantity END) as stock')
            ->value('stock') ?? 0;
    }
}
