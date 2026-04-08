<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\BookingStatus;
use App\Enums\PartnerKycStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\BookingResource;
use App\Http\Resources\API\V1\PaymentResource;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\PartnerKyc;
use App\Models\Payment;
use App\Models\User;
use App\Support\AdminCityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can access the dashboard.');

        if ($actor instanceof Owner) {
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

            $platformRevenue = (float) Payment::query()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->where('payment_type', PaymentType::Advance->value)
                ->sum('amount');
            $partnerEarnings = (float) Payment::query()
                ->where('payment_status', PaymentStatus::Paid->value)
                ->where('payment_type', PaymentType::Final->value)
                ->sum('amount');

            return $this->success([
                'stats' => [
                    'total_users' => User::count(),
                    'total_partners' => Partner::count(),
                    'total_bookings' => Booking::count(),
                    'pending_bookings' => Booking::where('status', BookingStatus::Pending->value)->count(),
                    'confirmed_bookings' => Booking::where('status', BookingStatus::Confirmed->value)->count(),
                    'completed_bookings' => Booking::where('status', BookingStatus::Completed->value)->count(),
                    'pending_payments' => Payment::where('payment_status', PaymentStatus::Pending->value)->count(),
                    'platform_revenue' => $platformRevenue,
                    'partner_earnings' => $partnerEarnings,
                    'paid_revenue' => $platformRevenue + $partnerEarnings,
                    'pending_kyc_count' => PartnerKyc::query()
                        ->where('status', PartnerKycStatus::Pending)
                        ->count(),
                ],
                'recent_bookings' => BookingResource::collection($recentBookings),
                'recent_payments' => PaymentResource::collection($recentPayments),
            ], 'Owner dashboard fetched');
        }

        $admin = $actor;
        $admin->loadMissing('city');

        $bookingsBase = AdminCityScope::bookings(Booking::query(), $admin);
        $paymentsBase = AdminCityScope::payments(Payment::query(), $admin);
        $partnersBase = AdminCityScope::partners(Partner::query(), $admin);
        $usersBase = AdminCityScope::users(User::query(), $admin);

        $recentBookings = (clone $bookingsBase)
            ->with(['user', 'category', 'plan', 'assignedPartner'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = (clone $paymentsBase)
            ->with(['booking.user', 'booking.plan'])
            ->latest()
            ->limit(8)
            ->get();

        $pendingKycCount = PartnerKyc::query()
            ->where('status', PartnerKycStatus::Pending)
            ->whereHas('partner', fn ($q) => AdminCityScope::partners($q, $admin))
            ->count();

        $platformRevenue = (float) (clone $paymentsBase)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('payment_type', PaymentType::Advance->value)
            ->sum('amount');
        $partnerEarnings = (float) (clone $paymentsBase)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('payment_type', PaymentType::Final->value)
            ->sum('amount');

        return $this->success([
            'stats' => [
                'total_users' => (clone $usersBase)->count(),
                'total_partners' => (clone $partnersBase)->count(),
                'total_bookings' => (clone $bookingsBase)->count(),
                'pending_bookings' => (clone $bookingsBase)->where('status', BookingStatus::Pending->value)->count(),
                'confirmed_bookings' => (clone $bookingsBase)->where('status', BookingStatus::Confirmed->value)->count(),
                'completed_bookings' => (clone $bookingsBase)->where('status', BookingStatus::Completed->value)->count(),
                'pending_payments' => (clone $paymentsBase)->where('payment_status', PaymentStatus::Pending->value)->count(),
                'platform_revenue' => $platformRevenue,
                'partner_earnings' => $partnerEarnings,
                'paid_revenue' => $platformRevenue + $partnerEarnings,
                'pending_kyc_count' => $pendingKycCount,
            ],
            'recent_bookings' => BookingResource::collection($recentBookings),
            'recent_payments' => PaymentResource::collection($recentPayments),
        ], 'Owner dashboard fetched');
    }
}
