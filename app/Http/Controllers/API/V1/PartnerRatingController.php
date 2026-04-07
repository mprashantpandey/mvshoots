<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PartnerRatingResource;
use App\Models\Partner;
use App\Models\PartnerRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerRatingController extends Controller
{
    use ApiResponse;

    /**
     * Ratings received by the authenticated partner (for partner app).
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can view their ratings.');

        $ratings = PartnerRating::query()
            ->where('partner_id', $actor->id)
            ->with([
                'booking:id,booking_date,plan_id,assigned_partner_id',
                'booking.plan:id,title',
                'user:id,name',
            ])
            ->latest()
            ->paginate(20);

        return $this->success(
            PartnerRatingResource::collection($ratings)->response()->getData(true),
            'Ratings fetched'
        );
    }
}
