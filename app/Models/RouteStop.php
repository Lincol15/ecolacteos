<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    protected $fillable = [
        'collection_route_id',
        'producer_id',
        'stop_order',
        'estimated_arrival',
        'estimated_liters',
        'special_instructions',
        'status',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_liters' => 'decimal:2',
            'visited_at' => 'datetime',
        ];
    }

    public const STATUS = [
        'pendiente' => 'Pendiente',
        'en_camino' => 'En Camino',
        'visitado' => 'Visitado',
        'ausente' => 'Ausente',
        'rechazado' => 'Rechazado',
    ];

    public function collectionRoute()
    {
        return $this->belongsTo(CollectionRoute::class);
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function milkDeliveries()
    {
        return $this->hasMany(MilkDelivery::class);
    }
}
