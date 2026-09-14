<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $fillable = [
        'payment_id',
        'milk_delivery_id',
        'liters',
        'price_per_liter',
        'line_amount',
    ];

    protected function casts(): array
    {
        return [
            'liters' => 'decimal:2',
            'price_per_liter' => 'decimal:2',
            'line_amount' => 'decimal:2',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function milkDelivery()
    {
        return $this->belongsTo(MilkDelivery::class);
    }
}
