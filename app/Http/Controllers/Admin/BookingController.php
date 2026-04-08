<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminCity;
use App\Http\Requests\Admin\BookingAssignPartnerRequest;
use App\Http\Requests\Admin\BookingStatusRequest;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Plan;
use App\Services\BookingService;
use App\Services\PartnerAssignmentService;
use App\Support\AdminCityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController
{
    use AuthorizesAdminCity;

    public function __construct(private readonly PartnerAssignmentService $partnerAssignmentService) {}

    public function index(Request $request): Response
    {
        $admin = Auth::guard('admin')->user();
        $filters = $request->only([
            'booking_id',
            'user',
            'partner_id',
            'category_id',
            'plan_id',
            'date',
            'payment_status',
            'status',
        ]);

        $bookingsQuery = AdminCityScope::bookings(Booking::query(), $admin)
            ->with(['user', 'plan', 'assignedPartner', 'category', 'payments'])
            ->filter($filters)
            ->latest();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookingsQuery
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Booking $booking) => $this->transformBooking($booking)),
            'partners' => AdminCityScope::partners(Partner::query(), $admin)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Partner $partner) => ['id' => $partner->id, 'name' => $partner->name]),
            'categories' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name]),
            'plans' => Plan::orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Plan $plan) => ['id' => $plan->id, 'title' => $plan->title]),
            'filters' => $filters,
            'statusOptions' => BookingStatus::values(),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $this->abortUnlessBookingInScope($booking);

        $booking->load(['user', 'category', 'plan', 'assignedPartner', 'payments', 'results.partner', 'statusLogs', 'partnerRating.user']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $this->transformBooking($booking, true),
            'partners' => Partner::query()
                ->where('status', 'active')
                ->kycVerified()
                ->when($booking->city_id, fn ($query) => $query->servingCity((int) $booking->city_id))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Partner $partner) => ['id' => $partner->id, 'name' => $partner->name]),
            'statusOptions' => BookingStatus::values(),
        ]);
    }

    public function assignPartner(BookingAssignPartnerRequest $request, Booking $booking): RedirectResponse
    {
        $this->abortUnlessBookingInScope($booking);

        $data = $request->validated();

        $this->partnerAssignmentService->assign($booking, $data['partner_id'], Auth::guard('admin')->user(), $data['remarks'] ?? null);

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'Partner assigned.');
    }

    public function updateStatus(BookingStatusRequest $request, Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $this->abortUnlessBookingInScope($booking);

        $data = $request->validated();

        $bookingService->updateStatus($booking, $data['status'], Auth::guard('admin')->user(), $data['remarks'] ?? null);

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'Booking status updated.');
    }

    private function transformBooking(Booking $booking, bool $detailed = false): array
    {
        $paymentStatuses = $booking->payments
            ->pluck('payment_status')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $payload = [
            'id' => $booking->id,
            'status' => $booking->status,
            'user_name' => $booking->user?->name,
            'user_phone' => $booking->user?->phone,
            'category_name' => $booking->category?->name,
            'plan_name' => $booking->plan?->title,
            'partner_id' => $booking->assigned_partner_id,
            'partner_name' => $booking->assignedPartner?->name,
            'booking_date' => optional($booking->booking_date)?->format('d M Y'),
            'booking_time' => $booking->booking_time,
            'address' => $booking->address,
            'payment_statuses' => $paymentStatuses,
            'show_url' => route('admin.bookings.show', $booking),
        ];

        if ($detailed) {
            $payload['notes'] = $booking->notes;
            $payload['total_amount'] = (string) $booking->total_amount;
            $payload['advance_amount'] = (string) $booking->advance_amount;
            $payload['final_amount'] = (string) $booking->final_amount;
            $payload['advance_paid'] = $booking->advance_paid;
            $payload['final_paid'] = $booking->final_paid;
            $payload['assign_url'] = route('admin.bookings.assign-partner', $booking);
            $payload['status_url'] = route('admin.bookings.update-status', $booking);
            $payload['payments'] = $booking->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'type' => $payment->payment_type,
                'status' => $payment->payment_status,
                'amount' => (string) $payment->amount,
                'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
                'show_url' => route('admin.payments.show', $payment),
            ])->values();
            $payload['status_logs'] = $booking->statusLogs
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($log) => [
                    'id' => $log->id,
                    'status' => $log->status,
                    'remarks' => $log->remarks,
                    'created_at' => optional($log->created_at)?->toDateTimeString(),
                ]);
            $payload['results'] = $booking->results->map(fn ($result) => [
                'id' => $result->id,
                'file_type' => $result->file_type,
                'file_url' => $result->file_url,
                'partner_name' => $result->partner?->name,
                'notes' => $result->notes,
                'created_at' => optional($result->created_at)?->toDateTimeString(),
            ])->values();
            $payload['partner_rating'] = $booking->partnerRating ? [
                'rating' => $booking->partnerRating->rating,
                'review' => $booking->partnerRating->review,
                'customer_name' => $booking->partnerRating->user?->name,
                'created_at' => optional($booking->partnerRating->created_at)?->toIso8601String(),
            ] : null;
        }

        return $payload;
    }
}
