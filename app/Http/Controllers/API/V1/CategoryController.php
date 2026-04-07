<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\CategoryResource;
use App\Http\Resources\API\V1\PlanResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $cityId = $this->resolveCityId($request);

        $categories = Category::query()
            ->where('status', 'active')
            ->whereHas('plans', function ($planQuery) use ($cityId): void {
                $planQuery
                    ->where('status', 'active')
                    ->when($cityId, function ($query, $resolvedCityId): void {
                        $query->where(function ($cityAware) use ($resolvedCityId): void {
                            $cityAware
                                ->whereDoesntHave('cities')
                                ->orWhereHas('cities', fn ($cityQuery) => $cityQuery->where('cities.id', $resolvedCityId));
                        });
                    });
            })
            ->with(['cities'])
            ->withCount(['plans', 'reels'])
            ->latest()
            ->get();

        return $this->success(CategoryResource::collection($categories), 'Categories fetched');
    }

    public function plans(Request $request, Category $category): JsonResponse
    {
        $cityId = $this->resolveCityId($request);

        $plans = $category->plans()
            ->where('status', 'active')
            ->when($cityId, function ($query, $resolvedCityId): void {
                $query->where(function ($cityAware) use ($resolvedCityId): void {
                    $cityAware
                        ->whereDoesntHave('cities')
                        ->orWhereHas('cities', fn ($cityQuery) => $cityQuery->where('cities.id', $resolvedCityId));
                });
            })
            ->with(['category', 'cities'])
            ->latest()
            ->get();

        return $this->success(PlanResource::collection($plans), 'Plans fetched');
    }

    private function resolveCityId(Request $request): ?int
    {
        if ($request->filled('city_id')) {
            return $request->integer('city_id');
        }

        $actor = $request->user('sanctum');
        if ($actor instanceof User && $actor->city_id) {
            return (int) $actor->city_id;
        }

        return null;
    }
}
