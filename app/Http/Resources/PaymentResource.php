<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producer_id' => $this->producer_id,
            'producer_name' => $this->whenLoaded('producer', fn () => $this->producer?->user?->fullname),
            'period_code' => $this->period_code,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'payment_date' => $this->payment_date?->toDateString(),
            'total_liters' => (float) $this->total_liters,
            'price_per_liter' => (float) ($this->precio_por_litro ?? $this->avg_price_per_liter),
            'detalle_diario' => (object) ($this->detalle_diario ?? []),
            'deductions' => (float) $this->deductions,
            'llevado_a_planta' => (float) $this->llevado_a_planta,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
        ];
    }
}
