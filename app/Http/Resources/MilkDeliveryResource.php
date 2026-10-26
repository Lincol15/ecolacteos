<?php

namespace App\Http\Resources;

use App\Models\MilkDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MilkDelivery
 */
class MilkDeliveryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_uuid' => $this->client_uuid,
            'producer_id' => $this->producer_id,
            'producer_name' => $this->whenLoaded('producer', fn () => $this->producer?->user?->fullname),
            'collector_id' => $this->collector_id,
            'collector_name' => $this->whenLoaded('collector', fn () => $this->collector?->fullname),
            'collection_route_id' => $this->collection_route_id,
            'route_stop_id' => $this->route_stop_id,
            'zona' => $this->zona,
            'liters' => (float) $this->liters,
            'temperature' => $this->temperature !== null ? (float) $this->temperature : null,
            'price_per_liter' => (float) $this->price_per_liter,
            'total_amount' => (float) $this->total_amount,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'delivery_time' => $this->delivery_time?->format('H:i'),
            'vehicle_plate' => $this->vehicle_plate,
            'observations' => $this->observations,
            'status' => $this->status,
            'recibido' => (bool) $this->recibido,
            'has_quality_analysis' => (bool) $this->has_quality_analysis,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
