<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
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
        'order_status',
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
        'pedido_web' => 'Pedido Web',
    ];

    /** Estados de entrega de un pedido web, en orden. */
    public const ORDER_STATUS = [
        'nuevo' => 'Nuevo',
        'confirmado' => 'Confirmado',
        'en_camino' => 'En camino',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    /** Siguiente paso de cada estado (los finales no avanzan). */
    public const NEXT_ORDER_STATUS = [
        'nuevo' => 'confirmado',
        'confirmado' => 'en_camino',
        'en_camino' => 'entregado',
    ];

    public function nextOrderStatus(): ?string
    {
        return self::NEXT_ORDER_STATUS[$this->order_status] ?? null;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, ['nuevo', 'confirmado', 'en_camino'], true);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'related_sale_id');
    }
}
