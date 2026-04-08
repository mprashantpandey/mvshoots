<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerDashboardController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can access this dashboard.');

        $partnerId = $actor->id;

        $assignedBase = Booking::query()->where('assigned_partner_id', $partnerId);

        $finalEarningsReceived = (float) Payment::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('payment_type', PaymentType::Final->value)
            ->whereHas('booking', fn ($q) => $q->where('assigned_partner_id', $partnerId))
            ->sum('amount');

        $finalPendingCollection = (float) (clone $assignedBase)
            ->where('final_paid', false)
            ->sum('final_amount');

        $assignedBookingsCount = (clone $assignedBase)->count();
        $completedBookingsCount = (clone $assignedBase)
            ->where('status', BookingStatus::Completed->value)
            ->count();

        return $this->success([
            'stats' => [
                'final_earnings_received' => $finalEarningsReceived,
                'final_pending_collection' => $finalPendingCollection,
                'assigned_bookings_count' => $assignedBookingsCount,
                'completed_bookings_count' => $completedBookingsCount,
            ],
        ], 'Partner dashboard fetched');
    }
}
