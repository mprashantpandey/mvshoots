<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\CategoryResource;
use App\Http\Resources\API\V1\PlanResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->withCount(['plans', 'reels'])
            ->latest()
            ->get();

        return $this->success(CategoryResource::collection($categories), 'Categories fetched');
    }

    public function plans(Category $category): JsonResponse
    {
        $plans = $category->plans()
            ->where('status', 'active')
            ->with('category')
            ->latest()
            ->get();

        return $this->success(PlanResource::collection($plans), 'Plans fetched');
    }
}
