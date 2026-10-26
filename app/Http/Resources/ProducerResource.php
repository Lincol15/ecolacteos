<?php

namespace App\Http\Resources;

use App\Models\Producer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Producer
 */
class ProducerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->user?->name,
            'lastname' => $this->user?->lastname,
            'dni' => $this->user?->dni,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'address' => $this->user?->address,
            'farm_name' => $this->farm_name,
            'comunidad' => $this->comunidad,
            'zone' => $this->zone,
            'district' => $this->district,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'cows_count' => (int) $this->cows_count,
            'daily_avg_liters' => (float) $this->daily_avg_liters,
            'status' => $this->status,
            'registration_date' => $this->registration_date?->toDateString(),
        ];
    }
}
