<?php

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Complaint
 */
class ComplaintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'producer_id' => $this->producer_id,
            'producer_name' => $this->whenLoaded('producer', fn () => $this->producer?->user?->fullname),
            'category' => $this->category,
            'priority' => $this->priority,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'staff_response' => $this->staff_response,
            'created_at' => $this->created_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
        ];
    }
}
