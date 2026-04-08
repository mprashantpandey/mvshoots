<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ProfileResource;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\Partner;
use App\Support\AdminCityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can view partners.');

        $partners = Partner::query()
            ->kycVerified()
            ->with(['managedCity', 'serviceCities', 'kyc'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->when($actor instanceof Admin, fn ($q) => AdminCityScope::partners($q, $actor))
            ->filter([
                'search' => $request->filled('search') ? $request->string('search')->toString() : null,
                'status' => $request->filled('status') ? $request->string('status')->toString() : null,
                'city_id' => $request->filled('city_id') ? $request->integer('city_id') : null,
            ])
            ->withCount('assignedBookings')
            ->orderBy('name')
            ->paginate(20);

        return $this->success(ProfileResource::collection($partners)->response()->getData(true), 'Partners fetched');
    }
}
