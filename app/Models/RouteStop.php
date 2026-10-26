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

    /**
     * Actualiza el estado de la parada; registra la hora de visita la primera vez que se marca como visitada.
     *
     * @param  array{status: string, estimated_liters?: float|int|string|null}  $data
     */
    public function updateStatus(array $data): self
    {
        if ($data['status'] === 'visitado' && ! $this->visited_at) {
            $data['visited_at'] = now();
        }
        $this->update($data);

        return $this;
    }

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
