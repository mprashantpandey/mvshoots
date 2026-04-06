<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ProfileResource;
use App\Models\Owner;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner, 403, 'Only owners can view partners.');

        $partners = Partner::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount('assignedBookings')
            ->orderBy('name')
            ->paginate(20);

        return $this->success(ProfileResource::collection($partners)->response()->getData(true), 'Partners fetched');
    }
}
