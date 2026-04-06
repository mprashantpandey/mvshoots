<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'payment_type' => $this->payment_type,
            'amount' => (float) $this->amount,
            'payment_status' => $this->payment_status,
            'payment_reference' => $this->payment_reference,
            'paid_at' => $this->paid_at?->toISOString(),
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
