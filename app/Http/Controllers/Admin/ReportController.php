<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController
{
    public function __invoke(Request $request): Response|StreamedResponse
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $bookings = Booking::query()
            ->when($from, fn ($query) => $query->whereDate('booking_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('booking_date', '<=', $to));

        $payments = Payment::query()
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

        $partnerPerformance = Partner::withCount('assignedBookings')
            ->withCount(['assignedBookings as completed_bookings_count' => fn ($query) => $query->where('status', 'completed')])
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
                'revenue' => (string) (clone $payments)->where('payment_status', 'paid')->sum('amount'),
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
