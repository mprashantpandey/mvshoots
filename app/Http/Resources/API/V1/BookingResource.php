<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'category_id' => (int) $this->category_id,
            'plan_id' => (int) $this->plan_id,
            'assigned_partner_id' => $this->assigned_partner_id === null ? null : (int) $this->assigned_partner_id,
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => $this->booking_time,
            'address' => $this->address,
            'notes' => $this->notes,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'advance_amount' => (float) $this->advance_amount,
            'final_amount' => (float) $this->final_amount,
            'advance_paid' => (bool) $this->advance_paid,
            'final_paid' => (bool) $this->final_paid,
            'user' => new ProfileResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'assigned_partner' => new ProfileResource($this->whenLoaded('assignedPartner')),
            'status_logs' => BookingStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'results' => BookingResultResource::collection($this->whenLoaded('results')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
