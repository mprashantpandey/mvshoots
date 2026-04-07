<?php

namespace App\Http\Resources\API\V1;

use App\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $actor = $request->user('sanctum');
        $resultsLocked = $actor instanceof User && ! (bool) $this->final_paid;

        $hasRating = $this->relationLoaded('partnerRating') && $this->partnerRating !== null;
        $isBookingOwner = $actor instanceof User && (int) $this->user_id === (int) $actor->id;

        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'city_id' => $this->city_id === null ? null : (int) $this->city_id,
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
            'results_count' => (int) ($this->results_count ?? $this->results()->count()),
            'partner_rating' => $this->when(
                $hasRating,
                fn () => new PartnerRatingResource($this->partnerRating)
            ),
            'can_rate_partner' => $isBookingOwner
                && $this->status === BookingStatus::Completed->value
                && $this->assigned_partner_id !== null,
            'has_rated_partner' => $isBookingOwner && $hasRating,
            'user' => new ProfileResource($this->whenLoaded('user')),
            'city' => new CityResource($this->whenLoaded('city')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'assigned_partner' => new ProfileResource($this->whenLoaded('assignedPartner')),
            'status_logs' => BookingStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'results_locked' => $resultsLocked,
            'results' => $resultsLocked
                ? []
                : BookingResultResource::collection($this->whenLoaded('results')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
