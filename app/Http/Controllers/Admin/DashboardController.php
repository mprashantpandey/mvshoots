<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerKycStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\PartnerKyc;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Reel;
use App\Models\User;
use App\Support\AdminCityScope;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(): Response
    {
        $admin = Auth::guard('admin')->user();
        $admin->loadMissing('city');

        $bookingsBase = AdminCityScope::bookings(Booking::query(), $admin);
        $paymentsBase = AdminCityScope::payments(Payment::query(), $admin);
        $partnersBase = AdminCityScope::partners(Partner::query(), $admin);
        $usersBase = AdminCityScope::users(User::query(), $admin);

        $recentBookings = (clone $bookingsBase)
            ->with(['user', 'plan', 'assignedPartner'])
            ->latest()
            ->take(6)
            ->get();

        $recentPayments = (clone $paymentsBase)
            ->with('booking.user')
            ->latest()
            ->take(6)
            ->get();

        $bookingChart = (clone $bookingsBase)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->latest('date')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        $advanceTotals = (clone $paymentsBase)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('payment_type', PaymentType::Advance->value)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $finalTotals = (clone $paymentsBase)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('payment_type', PaymentType::Final->value)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dateKeys = $advanceTotals->keys()->merge($finalTotals->keys())->unique()->sort()->values();
        $chartDates = $dateKeys->count() > 7 ? $dateKeys->slice(-7)->values() : $dateKeys;

        $revenueChart = $chartDates->map(fn ($date) => [
            'date' => $date,
            'platform' => (float) ($advanceTotals[$date] ?? 0),
            'partner' => (float) ($finalTotals[$date] ?? 0),
        ])->values();

        $pendingKycCount = PartnerKyc::query()
            ->where('status', PartnerKycStatus::Pending)
            ->whereHas('partner', fn ($q) => AdminCityScope::partners($q, $admin))
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'is_super_admin' => $admin->isSuperAdmin(),
            'admin_city_name' => $admin->city?->name,
            'totalBookings' => (clone $bookingsBase)->count(),
            'pendingBookings' => (clone $bookingsBase)->where('status', 'pending')->count(),
            'completedBookings' => (clone $bookingsBase)->where('status', 'completed')->count(),
            'totalUsers' => (clone $usersBase)->count(),
            'totalPartners' => (clone $partnersBase)->count(),
            'totalCategories' => $admin->isSuperAdmin() ? Category::count() : null,
            'totalPlans' => $admin->isSuperAdmin() ? Plan::count() : null,
            'totalReels' => $admin->isSuperAdmin() ? Reel::count() : null,
            'platformRevenue' => (float) (clone $paymentsBase)
                ->where('payment_status', PaymentStatus::Paid->value)
                ->where('payment_type', PaymentType::Advance->value)
                ->sum('amount'),
            'partnerEarnings' => (float) (clone $paymentsBase)
                ->where('payment_status', PaymentStatus::Paid->value)
                ->where('payment_type', PaymentType::Final->value)
                ->sum('amount'),
            'pendingPayments' => (clone $paymentsBase)->where('payment_status', 'pending')->count(),
            'pendingKycCount' => $pendingKycCount,
            'recentBookings' => $recentBookings->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'user_name' => $booking->user?->name,
                'plan_title' => $booking->plan?->title,
                'status' => $booking->status,
                'partner_name' => $booking->assignedPartner?->name ?? 'Unassigned',
                'show_url' => route('admin.bookings.show', $booking),
            ])->values(),
            'recentPayments' => $recentPayments->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'user_name' => $payment->booking?->user?->name,
                'payment_type' => $payment->payment_type,
                'amount' => (float) $payment->amount,
                'show_url' => route('admin.payments.show', $payment),
            ])->values(),
            'bookingChart' => $bookingChart->map(fn ($point) => [
                'date' => $point->date,
                'total' => (int) $point->total,
            ])->values(),
            'revenueChart' => $revenueChart,
        ]);
    }
}
