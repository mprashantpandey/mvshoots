<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'booking_id' => (int) $this->booking_id,
            'rating' => (int) $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'booking_date' => $this->whenLoaded('booking', fn () => $this->booking->booking_date?->toDateString()),
            'plan_title' => $this->whenLoaded('booking', fn () => $this->booking->relationLoaded('plan') ? $this->booking->plan?->title : null),
            'customer_display_name' => $this->whenLoaded('user', fn () => $this->maskedCustomerName($this->user?->name)),
        ];
    }

    private function maskedCustomerName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'Customer';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        if (count($parts) === 1) {
            return $parts[0];
        }

        $first = $parts[0];
        $last = end($parts);
        $initial = $last !== '' ? strtoupper(mb_substr($last, 0, 1)).'.' : '';

        return trim("{$first} {$initial}");
    }
}
