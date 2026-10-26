<?php

namespace App\Http\Resources;

use App\Models\CollectionRoute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionRoute
 */
class CollectionRouteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'day' => $this->day,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'vehicle_plate' => $this->vehicle_plate,
            'estimated_distance_km' => $this->estimated_distance_km !== null ? (float) $this->estimated_distance_km : null,
            'collector_id' => $this->collector_id,
            'status' => $this->status,
            'stops' => RouteStopResource::collection($this->whenLoaded('stops')),
        ];
    }
}
