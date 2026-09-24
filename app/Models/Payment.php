<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id',
        'period_code',
        'period_start',
        'period_end',
        'payment_date',
        'total_liters',
        'detalle_diario',
        'avg_price_per_liter',
        'precio_por_litro',
        'base_amount',
        'quality_bonus',
        'production_bonus',
        'deductions',
        'llevado_a_planta',
        'total_amount',
        'deductions_detail',
        'bonus_detail',
        'payment_method',
        'transaction_number',
        'status',
        'processed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payment_date' => 'date',
            'total_liters' => 'decimal:2',
            'detalle_diario' => 'array',
            'avg_price_per_liter' => 'decimal:2',
            'precio_por_litro' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'quality_bonus' => 'decimal:2',
            'production_bonus' => 'decimal:2',
            'deductions' => 'decimal:2',
            'llevado_a_planta' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public const STATUS = [
        'pendiente' => 'Pendiente',
        'procesando' => 'Procesando',
        'pagado' => 'Pagado',
        'parcial' => 'Parcial',
        'rechazado' => 'Rechazado',
    ];

    public const PAYMENT_METHODS = [
        'transferencia' => 'Transferencia',
        'efectivo' => 'Efectivo',
        'cheque' => 'Cheque',
    ];

    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        $start = $this->period_start?->format('d/m/Y');
        $end = $this->period_end?->format('d/m/Y');

        return "{$start} - {$end}";
    }

    public function getReceiptNumberAttribute(): string
    {
        return 'LIQ-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getBonusTotalAttribute(): float
    {
        return (float) $this->quality_bonus + (float) $this->production_bonus;
    }

    public function getDiscountTotalAttribute(): float
    {
        return (float) $this->deductions + (float) $this->llevado_a_planta;
    }
}
