<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payment;
use App\Support\AdminCityScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController
{
    public function __invoke(Request $request): Response|StreamedResponse
    {
        $admin = Auth::guard('admin')->user();
        $from = $request->date('from');
        $to = $request->date('to');

        $bookings = AdminCityScope::bookings(Booking::query(), $admin)
            ->when($from, fn ($query) => $query->whereDate('booking_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('booking_date', '<=', $to));

        $payments = AdminCityScope::payments(Payment::query(), $admin)
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to));

        if ($request->boolean('export')) {
            return response()->streamDownload(function () use ($bookings): void {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Booking ID', 'User ID', 'Status', 'Date', 'Total Amount']);
                foreach ($bookings->with('user')->get() as $booking) {
                    fputcsv($handle, [$booking->id, $booking->user_id, $booking->status, $booking->booking_date, $booking->total_amount]);
                }
                fclose($handle);
            }, 'booking-report.csv');
        }

        $partnerPerformance = AdminCityScope::partners(Partner::query(), $admin)
            ->withCount([
                'assignedBookings as assigned_bookings_count' => fn ($query) => $admin->city_id
                    ? $query->where('city_id', $admin->city_id)
                    : $query,
            ])
            ->withCount([
                'assignedBookings as completed_bookings_count' => fn ($query) => $query
                    ->where('status', 'completed')
                    ->when($admin->city_id, fn ($q) => $q->where('city_id', $admin->city_id)),
            ])
            ->orderByDesc('assigned_bookings_count')
            ->take(10)
            ->get();

        return Inertia::render('Admin/Reports/Index', [
            'bookingStatusCounts' => (clone $bookings)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->map(fn ($count, $status) => [
                    'status' => $status,
                    'count' => $count,
                ])
                ->values(),
            'paymentTypeTotals' => (clone $payments)
                ->where('payment_status', 'paid')
                ->selectRaw('payment_type, SUM(amount) as total')
                ->groupBy('payment_type')
                ->pluck('total', 'payment_type')
                ->map(fn ($total, $type) => [
                    'type' => $type,
                    'total' => (string) $total,
                ])
                ->values(),
            'totals' => [
                'bookings' => (clone $bookings)->count(),
                'platform_revenue' => (string) (clone $payments)
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->where('payment_type', PaymentType::Advance->value)
                    ->sum('amount'),
                'partner_earnings' => (string) (clone $payments)
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->where('payment_type', PaymentType::Final->value)
                    ->sum('amount'),
                'revenue' => (string) (clone $payments)->where('payment_status', PaymentStatus::Paid->value)->sum('amount'),
                'payments' => (clone $payments)->count(),
            ],
            'partnerPerformance' => $partnerPerformance->map(fn ($partner) => [
                'id' => $partner->id,
                'name' => $partner->name,
                'assigned_bookings_count' => $partner->assigned_bookings_count,
                'completed_bookings_count' => $partner->completed_bookings_count,
            ]),
            'filters' => [
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
        ]);
    }
}
