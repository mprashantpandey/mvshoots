<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;

        if ($this->image) {
            $imageUrl = str($this->image)->startsWith('http')
                ? $this->image
                : asset('storage/'.$this->image);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image' => $imageUrl,
            'app_target' => $this->app_target,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
        ];
    }
}
