<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MilkDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id',
        'collector_id',
        'route_stop_id',
        'collection_route_id',
        'liters',
        'temperature',
        'price_per_liter',
        'total_amount',
        'delivery_date',
        'delivery_time',
        'container_type',
        'containers_count',
        'vehicle_plate',
        'observations',
        'has_quality_analysis',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'liters' => 'decimal:2',
            'temperature' => 'decimal:2',
            'price_per_liter' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'delivery_date' => 'date',
            'delivery_time' => 'datetime',
            'has_quality_analysis' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public const STATUS = [
        'registrado' => 'Registrado',
        'aceptado' => 'Aceptado',
        'rechazado' => 'Rechazado',
        'analizado' => 'Analizado',
    ];

    public const CONTAINER_TYPES = [
        'caneca' => 'Caneca',
        'bidon' => 'Bidón',
        'cisterna' => 'Cisterna',
    ];

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function routeStop()
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function collectionRoute()
    {
        return $this->belongsTo(CollectionRoute::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function qualityReport()
    {
        return $this->hasOne(QualityReport::class);
    }

    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function batches()
    {
        return $this->belongsToMany(ProductionBatch::class, 'batch_milk_usage')
            ->withPivot('liters_used');
    }
}
