<?php

namespace App\Http\Resources;

use App\Models\QualityReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QualityReport
 */
class QualityReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $decimal = fn ($value) => $value !== null ? (float) $value : null;
        $producer = $this->producer ?? $this->milkDelivery?->producer;

        return [
            'id' => $this->id,
            'sample_code' => $this->sample_code,
            'milk_delivery_id' => $this->milk_delivery_id,
            'producer_id' => $producer?->id,
            'producer_name' => $producer?->user?->fullname,
            'analyst_id' => $this->analyst_id,
            'origen_datos' => $this->origen_datos,
            'temperatura' => $decimal($this->temperatura),
            'grasa_pct' => $decimal($this->grasa_pct),
            'proteina_pct' => $decimal($this->proteina_pct),
            'lactosa_pct' => $decimal($this->lactosa_pct),
            'solidos_no_grasos_pct' => $decimal($this->solidos_no_grasos_pct),
            'total_solidos_pct' => $decimal($this->total_solidos_pct),
            'agua_aniadida_pct' => $decimal($this->agua_aniadida_pct),
            'ph' => $decimal($this->ph),
            'punto_congelacion' => $decimal($this->punto_congelacion),
            'densidad' => $decimal($this->densidad),
            'observations' => $this->observations,
            'result' => $this->result,
            'rejection_reason' => $this->rejection_reason,
            'analyzed_at' => $this->analyzed_at?->toIso8601String(),
        ];
    }
}
