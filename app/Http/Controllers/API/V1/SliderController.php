<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\API\V1\SliderResource;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SliderController
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app' => ['required', 'in:user,partner'],
        ]);

        $app = $validated['app'];

        $sliders = Slider::query()
            ->active()
            ->whereIn('app_target', [$app, 'both'])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => SliderResource::collection($sliders),
        ]);
    }
}
