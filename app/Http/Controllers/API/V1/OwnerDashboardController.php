<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\BookingResource;
use App\Http\Resources\API\V1\PaymentResource;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can access the dashboard.');

        $recentBookings = Booking::query()
            ->with(['user', 'category', 'plan', 'assignedPartner'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = Payment::query()
            ->with(['booking.user', 'booking.plan'])
            ->latest()
            ->limit(8)
            ->get();

        return $this->success([
            'stats' => [
                'total_users' => User::count(),
                'total_partners' => Partner::count(),
                'total_bookings' => Booking::count(),
                'pending_bookings' => Booking::where('status', BookingStatus::Pending->value)->count(),
                'confirmed_bookings' => Booking::where('status', BookingStatus::Confirmed->value)->count(),
                'completed_bookings' => Booking::where('status', BookingStatus::Completed->value)->count(),
                'pending_payments' => Payment::where('payment_status', PaymentStatus::Pending->value)->count(),
                'paid_revenue' => (float) Payment::where('payment_status', PaymentStatus::Paid->value)->sum('amount'),
            ],
            'recent_bookings' => BookingResource::collection($recentBookings),
            'recent_payments' => PaymentResource::collection($recentPayments),
        ], 'Owner dashboard fetched');
    }
}
