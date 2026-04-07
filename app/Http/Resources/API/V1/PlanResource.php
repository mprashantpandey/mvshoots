<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'category_id' => $this->category_id === null ? null : (int) $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration' => $this->duration,
            'inclusions' => $this->inclusions ?? [],
            'status' => $this->status,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'city_ids' => $this->whenLoaded('cities', fn () => $this->cities->pluck('id')->map(fn ($id) => (int) $id)->values()->all()),
            'cities' => CityResource::collection($this->whenLoaded('cities')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
