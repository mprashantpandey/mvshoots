<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ReelResource;
use App\Models\Reel;
use Illuminate\Http\JsonResponse;

class ReelController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $reels = Reel::query()
            ->where('status', 'active')
            ->with('category')
            ->latest()
            ->get();

        return $this->success(ReelResource::collection($reels), 'Reels fetched');
    }

    public function show(Reel $reel): JsonResponse
    {
        $reel->load('category');

        return $this->success(new ReelResource($reel), 'Reel details fetched');
    }
}
