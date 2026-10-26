<?php

namespace App\Http\Resources;

use App\Models\RouteStop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RouteStop
 */
class RouteStopResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_route_id' => $this->collection_route_id,
            'stop_order' => $this->stop_order,
            'estimated_arrival' => $this->estimated_arrival,
            'estimated_liters' => (float) $this->estimated_liters,
            'special_instructions' => $this->special_instructions,
            'status' => $this->status,
            'visited_at' => $this->visited_at?->toIso8601String(),
            'producer' => new ProducerResource($this->whenLoaded('producer')),
        ];
    }
}
