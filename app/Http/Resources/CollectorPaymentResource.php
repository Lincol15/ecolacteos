<?php

namespace App\Http\Resources;

use App\Models\CollectorPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectorPayment
 */
class CollectorPaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collector_id' => $this->collector_id,
            'collector_name' => $this->whenLoaded('collector', fn () => $this->collector?->fullname),
            'period_code' => $this->period_code,
            'period_month' => $this->period_month ? substr((string) $this->period_month, 0, 10) : null,
            'base_salary' => (float) $this->base_salary,
            'total_amount' => (float) $this->total_amount,
            'liters_collected' => (float) $this->liters_collected,
            'deliveries_count' => (int) $this->deliveries_count,
            'status' => $this->status,
            'payment_date' => $this->payment_date ? substr((string) $this->payment_date, 0, 10) : null,
        ];
    }
}
