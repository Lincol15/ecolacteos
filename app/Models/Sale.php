<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'client_name',
        'client_dni_ruc',
        'client_email',
        'client_phone',
        'client_address',
        'sale_date',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'payment_status',
        'payment_method',
        'sale_type',
        'served_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public const PAYMENT_STATUS = [
        'pendiente' => 'Pendiente',
        'pagado' => 'Pagado',
        'parcial' => 'Parcial',
        'anulado' => 'Anulado',
    ];

    public const PAYMENT_METHODS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'yape' => 'Yape',
        'plin' => 'Plin',
        'cheque' => 'Cheque',
        'tarjeta' => 'Tarjeta',
    ];

    public const SALE_TYPES = [
        'mostrador' => 'Mostrador',
        'delivery' => 'Delivery',
        'mayorista' => 'Mayorista',
        'exportacion' => 'Exportación',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'related_sale_id');
    }
}
