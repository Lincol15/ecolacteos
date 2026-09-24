<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class MilkDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id',
        'collector_id',
        'route_stop_id',
        'collection_route_id',
        'zona',
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
        'recibido',
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
            'recibido' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public const STATUS = [
        'registrado' => 'Registrado',
        'aceptado' => 'Aceptado',
        'rechazado' => 'Rechazado',
        'analizado' => 'Analizado',
    ];

    public function getRecibidoLabelAttribute(): string
    {
        return $this->recibido ? 'Recibido en Planta' : 'En Tránsito';
    }

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

    /**
     * Leche recibida (entregas no rechazadas) por día en un rango de fechas.
     * Sin fechas, muestra solo el día de hoy. El rango se limita a 92 días.
     *
     * @return array{from: Carbon, to: Carbon, days: Collection<int, array{date: Carbon, liters: float, deliveries: int}>, total_liters: float, total_deliveries: int}
     */
    public static function receivedByDay(?string $from, ?string $to): array
    {
        $to = $to ? Carbon::parse($to)->startOfDay() : Carbon::today();
        $from = $from ? Carbon::parse($from)->startOfDay() : $to->copy();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > 91) {
            $from = $to->copy()->subDays(91);
        }

        $rows = self::where('status', '!=', 'rechazado')
            ->whereBetween('delivery_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('DATE(delivery_date) as day, SUM(liters) as liters, COUNT(*) as deliveries')
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->day)->toDateString());

        $days = collect();
        for ($day = $to->copy(); $day->gte($from); $day->subDay()) {
            $row = $rows->get($day->toDateString());
            $days->push([
                'date' => $day->copy(),
                'liters' => round((float) ($row->liters ?? 0), 2),
                'deliveries' => (int) ($row->deliveries ?? 0),
            ]);
        }

        return [
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'total_liters' => round($days->sum('liters'), 2),
            'total_deliveries' => $days->sum('deliveries'),
        ];
    }
}
