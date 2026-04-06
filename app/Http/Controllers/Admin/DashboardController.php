<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Reel;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(): Response
    {
        $recentBookings = Booking::with(['user', 'plan', 'assignedPartner'])
            ->latest()
            ->take(6)
            ->get();

        $recentPayments = Payment::with('booking.user')
            ->latest()
            ->take(6)
            ->get();

        $bookingChart = Booking::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->latest('date')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        $revenueChart = Payment::query()
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->latest('date')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'totalBookings' => Booking::count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'completedBookings' => Booking::where('status', 'completed')->count(),
            'totalUsers' => User::count(),
            'totalPartners' => Partner::count(),
            'totalCategories' => Category::count(),
            'totalPlans' => Plan::count(),
            'totalReels' => Reel::count(),
            'totalRevenue' => Payment::where('payment_status', 'paid')->sum('amount'),
            'pendingPayments' => Payment::where('payment_status', 'pending')->count(),
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
            'revenueChart' => $revenueChart->map(fn ($point) => [
                'date' => $point->date,
                'total' => (float) $point->total,
            ])->values(),
        ]);
    }
}
