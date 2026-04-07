<?php

namespace App\Http\Resources\API\V1;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $managedCity = method_exists($this->resource, 'managedCity') && $this->resource->relationLoaded('managedCity')
            ? $this->resource->managedCity
            : null;
        $serviceCities = method_exists($this->resource, 'serviceCities') && $this->resource->relationLoaded('serviceCities')
            ? $this->resource->serviceCities
            : null;

        $base = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'city' => $managedCity?->name ?? $this->city ?? null,
            'city_id' => $this->city_id === null ? null : (int) $this->city_id,
            'managed_city' => $this->city_id && $managedCity ? new CityResource($managedCity) : null,
            'service_city_ids' => $this->when($serviceCities !== null, fn () => $serviceCities->pluck('id')->map(fn ($id) => (int) $id)->values()->all()),
            'service_cities' => $this->when($serviceCities !== null, fn () => CityResource::collection($serviceCities)),
            'phone' => $this->phone ?? null,
            'firebase_uid' => $this->firebase_uid ?? null,
            'status' => $this->status ?? null,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];

        if ($this->resource instanceof User) {
            $base['account'] = [
                'status' => $this->status,
                'can_book' => $this->status === 'active',
            ];
        }

        if ($this->resource instanceof Partner) {
            $partner = $this->resource;
            $base['account'] = [
                'status' => $partner->status,
                'can_accept_bookings' => $partner->canAcceptServiceBookings(),
            ];
            $base['kyc'] = $this->partnerKycSummary($partner);
            $base['rating_average'] = $partner->ratings_avg_rating !== null
                ? round((float) $partner->ratings_avg_rating, 2)
                : null;
            $base['ratings_count'] = (int) ($partner->ratings_count ?? 0);
        }

        return $base;
    }

    private function partnerKycSummary(Partner $partner): PartnerKycResource|array
    {
        $kyc = $partner->relationLoaded('kyc') ? $partner->kyc : null;

        if (! $kyc) {
            return [
                'status' => 'not_submitted',
                'aadhar_number_masked' => null,
                'pan_number_masked' => null,
                'rejection_reason' => null,
                'submitted_at' => null,
                'reviewed_at' => null,
                'can_resubmit' => true,
            ];
        }

        return new PartnerKycResource($kyc);
    }
}
