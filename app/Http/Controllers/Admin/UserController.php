<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);

        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->withCount('bookings')
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (User $user) => $this->transformUser($user)),
            'filters' => $filters,
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $this->transformUser($user->loadCount('bookings'), true),
            'bookings' => $user->bookings()
                ->with(['plan', 'category', 'assignedPartner'])
                ->latest()
                ->paginate(10)
                ->through(fn ($booking) => [
                    'id' => $booking->id,
                    'plan_name' => $booking->plan?->title,
                    'category_name' => $booking->category?->name,
                    'status' => $booking->status,
                    'partner_name' => $booking->assignedPartner?->name,
                    'total_amount' => (string) $booking->total_amount,
                    'show_url' => route('admin.bookings.show', $booking),
                ]),
        ]);
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->update([
            'status' => $data['status'],
        ]);

        return redirect()->back()->with('status', 'User status updated.');
    }

    private function transformUser(User $user, bool $detailed = false): array
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => $user->status,
            'bookings_count' => $user->bookings_count ?? $user->bookings()->count(),
            'show_url' => route('admin.users.show', $user),
            'status_url' => route('admin.users.update-status', $user),
        ];

        if ($detailed) {
            $payload['firebase_uid'] = $user->firebase_uid;
        }

        return $payload;
    }
}
