<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PartnerRequest;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);

        return Inertia::render('Admin/Partners/Index', [
            'partners' => Partner::query()
                ->withCount('assignedBookings')
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Partner $partner) => $this->transformPartner($partner)),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Partners/Form', [
            'partner' => null,
            'submitUrl' => route('admin.partners.store'),
            'method' => 'post',
        ]);
    }

    public function store(PartnerRequest $request): RedirectResponse
    {
        Partner::create($request->validated());

        return redirect()->route('admin.partners.index')->with('status', 'Partner created.');
    }

    public function show(Partner $partner): Response
    {
        $partner->load(['assignedBookings.user', 'assignedBookings.plan', 'bookingResults']);

        return Inertia::render('Admin/Partners/Show', [
            'partner' => $this->transformPartner($partner->loadCount('assignedBookings'), true),
        ]);
    }

    public function edit(Partner $partner): Response
    {
        return Inertia::render('Admin/Partners/Form', [
            'partner' => $this->transformPartner($partner->loadCount('assignedBookings'), true),
            'submitUrl' => route('admin.partners.update', $partner),
            'method' => 'put',
        ]);
    }

    public function update(PartnerRequest $request, Partner $partner): RedirectResponse
    {
        $partner->update($request->validated());

        return redirect()->route('admin.partners.index')->with('status', 'Partner updated.');
    }

    public function updateStatus(Request $request, Partner $partner): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $partner->update([
            'status' => $data['status'],
        ]);

        return redirect()->back()->with('status', 'Partner status updated.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('status', 'Partner deleted.');
    }

    private function transformPartner(Partner $partner, bool $detailed = false): array
    {
        $payload = [
            'id' => $partner->id,
            'name' => $partner->name,
            'phone' => $partner->phone,
            'email' => $partner->email,
            'status' => $partner->status,
            'assigned_bookings_count' => $partner->assigned_bookings_count ?? $partner->assignedBookings()->count(),
            'show_url' => route('admin.partners.show', $partner),
            'edit_url' => route('admin.partners.edit', $partner),
            'delete_url' => route('admin.partners.destroy', $partner),
            'status_url' => route('admin.partners.update-status', $partner),
        ];

        if ($detailed) {
            $payload['assigned_bookings'] = $partner->assignedBookings->map(fn ($booking) => [
                'id' => $booking->id,
                'user_name' => $booking->user?->name,
                'plan_name' => $booking->plan?->title,
                'status' => $booking->status,
                'show_url' => route('admin.bookings.show', $booking),
            ])->values();
            $payload['booking_results'] = $partner->bookingResults->map(fn ($result) => [
                'id' => $result->id,
                'file_type' => $result->file_type,
                'notes' => $result->notes,
                'file_url' => $result->file_url,
            ])->values();
        }

        return $payload;
    }
}
