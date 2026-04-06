<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'user_type' => $this->user_type,
            'user_id' => (int) $this->user_id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'reference_id' => $this->reference_id === null ? null : (int) $this->reference_id,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
