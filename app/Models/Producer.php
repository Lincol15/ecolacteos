<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code',
        'farm_name',
        'zone',
        'district',
        'province',
        'region',
        'latitude',
        'longitude',
        'cows_count',
        'daily_avg_liters',
        'notes',
        'status',
        'registration_date',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'daily_avg_liters' => 'decimal:2',
            'registration_date' => 'date',
        ];
    }

    public const STATUS = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        'suspendido' => 'Suspendido',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'N/A';
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->zone, $this->district, $this->province, $this->region]);
        return implode(', ', $parts);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function milkDeliveries()
    {
        return $this->hasMany(MilkDelivery::class);
    }

    public function qualityReports()
    {
        return $this->hasManyThrough(QualityReport::class, MilkDelivery::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function routeStops()
    {
        return $this->hasMany(RouteStop::class);
    }

    public function collectionRoutes()
    {
        return $this->belongsToMany(CollectionRoute::class, 'route_stops');
    }

    public function getTotalLitersByPeriod($start, $end)
    {
        return $this->milkDeliveries()
            ->whereBetween('delivery_date', [$start, $end])
            ->sum('liters');
    }
}
