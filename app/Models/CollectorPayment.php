<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pago mensual (sueldo fijo) de un acopiador.
 */
class CollectorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'collector_id',
        'period_code',
        'period_month',
        'base_salary',
        'bonus',
        'deductions',
        'total_amount',
        'liters_collected',
        'deliveries_count',
        'status',
        'payment_method',
        'transaction_number',
        'payment_date',
        'processed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'payment_date' => 'date',
            'base_salary' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deductions' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'liters_collected' => 'decimal:2',
            'deliveries_count' => 'integer',
        ];
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        return ucfirst($this->period_month->locale('es')->translatedFormat('F Y'));
    }

    public function getReceiptNumberAttribute(): string
    {
        return 'BP-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
