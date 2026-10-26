<?php

namespace App\Http\Resources;

use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Sanction
 */
class SanctionResource extends JsonResource
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
            'issued_by' => $this->issued_by,
            'type' => $this->type,
            'motivo' => $this->motivo,
            'description' => $this->description,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'status' => $this->status,
            'sanction_date' => $this->sanction_date ? substr((string) $this->sanction_date, 0, 10) : null,
        ];
    }
}
