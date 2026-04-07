<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PartnerRatingResource;
use App\Models\Booking;
use App\Models\PartnerRating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingRatingController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user('sanctum');

        abort_unless($user instanceof User, 403, 'Only customers can rate partners.');

        if ((int) $booking->user_id !== (int) $user->id) {
            abort(403, 'You can only rate your own bookings.');
        }

        if ($booking->status !== BookingStatus::Completed->value) {
            throw ValidationException::withMessages([
                'booking' => ['You can only rate a partner after the booking is completed.'],
            ]);
        }

        if ($booking->assigned_partner_id === null) {
            throw ValidationException::withMessages([
                'booking' => ['This booking has no assigned partner to rate.'],
            ]);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $rating = PartnerRating::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $user->id,
                'partner_id' => (int) $booking->assigned_partner_id,
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]
        );

        return $this->success(
            new PartnerRatingResource($rating->fresh()),
            'Rating saved'
        );
    }
}
