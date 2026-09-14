<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'product_id',
        'input_milk_liters',
        'output_units',
        'yield_percentage',
        'production_date',
        'expiration_date',
        'supervised_by',
        'recipe_notes',
        'quality_notes',
        'status',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'input_milk_liters' => 'decimal:2',
            'output_units' => 'decimal:2',
            'yield_percentage' => 'decimal:2',
            'production_date' => 'date',
            'expiration_date' => 'date',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public const STATUS = [
        'planeado' => 'Planeado',
        'en_proceso' => 'En Proceso',
        'curando' => 'Curando',
        'terminado' => 'Terminado',
        'vendido' => 'Vendido',
        'desperdicio' => 'Desperdicio',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervised_by');
    }

    public function milkDeliveries()
    {
        return $this->belongsToMany(MilkDelivery::class, 'batch_milk_usage')
            ->withPivot('liters_used');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
