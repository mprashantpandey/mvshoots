<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingResultController
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search']);

        $bookings = Booking::with(['user', 'assignedPartner', 'results'])
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('id', $search)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('assignedPartner', fn ($partnerQuery) => $partnerQuery->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->when($request->string('status')->value() === 'uploaded', fn ($query) => $query->has('results'))
            ->when($request->string('status')->value() === 'pending', fn ($query) => $query->doesntHave('results'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/BookingResults/Index', [
            'bookings' => $bookings->through(fn (Booking $booking) => [
                'id' => $booking->id,
                'user_name' => $booking->user?->name,
                'partner_name' => $booking->assignedPartner?->name,
                'results_count' => $booking->results->count(),
                'results_uploaded' => $booking->results->isNotEmpty(),
                'show_url' => route('admin.booking-results.show', $booking),
            ]),
            'filters' => $filters,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['user', 'assignedPartner', 'results.partner']);

        return Inertia::render('Admin/BookingResults/Show', [
            'booking' => [
                'id' => $booking->id,
                'user_name' => $booking->user?->name,
                'partner_name' => $booking->assignedPartner?->name,
                'results_uploaded' => $booking->results->isNotEmpty(),
                'results' => $booking->results->map(fn ($result) => [
                    'id' => $result->id,
                    'file_type' => $result->file_type,
                    'partner_name' => $result->partner?->name,
                    'notes' => $result->notes,
                    'file_url' => $result->file_url,
                ])->values(),
            ],
        ]);
    }
}
