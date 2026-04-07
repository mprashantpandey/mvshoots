<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    use ApiResponse;

    public function show(Request $request, Plan $plan): JsonResponse
    {
        $cityId = $request->integer('city_id');

        if ($cityId && $plan->cities()->exists() && ! $plan->cities()->where('cities.id', $cityId)->exists()) {
            abort(404, 'This plan is not available in the selected city.');
        }

        $plan->load(['category', 'cities']);

        return $this->success(new PlanResource($plan), 'Plan details fetched');
    }
}
