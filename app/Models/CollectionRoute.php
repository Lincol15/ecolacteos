<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionRoute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'day',
        'start_time',
        'end_time',
        'vehicle_plate',
        'estimated_distance_km',
        'collector_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_distance_km' => 'decimal:2',
        ];
    }

    public const DAYS = [
        'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo',
    ];

    public const STATUS = [
        'planeada' => 'Planeada',
        'en_curso' => 'En Curso',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
    ];

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class)->orderBy('stop_order');
    }

    public function milkDeliveries()
    {
        return $this->hasMany(MilkDelivery::class);
    }

    public function totalStops(): int
    {
        return $this->stops()->count();
    }

    public function visitedStops(): int
    {
        return $this->stops()->where('status', 'visitado')->count();
    }

    public function estimatedLiters(): float
    {
        return (float) $this->stops()->sum('estimated_liters');
    }

    public function collectedLiters(): float
    {
        return (float) $this->milkDeliveries()->sum('liters');
    }

    public function progressPercentage(): float
    {
        $total = $this->totalStops();
        if ($total === 0) {
            return 0;
        }
        return round(($this->visitedStops() / $total) * 100, 1);
    }
}
