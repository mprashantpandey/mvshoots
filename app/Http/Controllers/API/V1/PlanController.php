<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    use ApiResponse;

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('category');

        return $this->success(new PlanResource($plan), 'Plan details fetched');
    }
}
