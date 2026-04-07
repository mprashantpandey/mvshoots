<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $managedCity = method_exists($this->resource, 'managedCity') && $this->resource->relationLoaded('managedCity')
            ? $this->resource->managedCity
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'city' => $managedCity?->name ?? $this->city ?? null,
            'city_id' => $this->city_id === null ? null : (int) $this->city_id,
            'managed_city' => $this->city_id && $managedCity ? new CityResource($managedCity) : null,
            'phone' => $this->phone ?? null,
            'firebase_uid' => $this->firebase_uid ?? null,
            'status' => $this->status ?? null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
