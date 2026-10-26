<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'fullname' => $this->fullname,
            'dni' => $this->dni,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'role_label' => $this->role_label,
            'active' => $this->active,
            'comunidad' => $this->comunidad,
            'vehiculo' => $this->vehiculo,
            'producer_id' => $this->whenLoaded('producer', fn () => $this->producer?->id),
        ];
    }
}
