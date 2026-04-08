<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminCity;
use App\Models\User;
use App\Support\AdminCityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController
{
    use AuthorizesAdminCity;

    public function index(Request $request): Response
    {
        $admin = Auth::guard('admin')->user();
        $filters = $request->only(['search', 'status']);

        $userQuery = AdminCityScope::users(User::query(), $admin);

        if ($admin->city_id) {
            $userQuery->withCount(['bookings' => fn ($q) => $q->where('city_id', $admin->city_id)]);
        } else {
            $userQuery->withCount('bookings');
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $userQuery
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
        $this->abortUnlessUserInScope($user);

        $admin = Auth::guard('admin')->user();
        $bookingsQuery = $user->bookings()->with(['plan', 'category', 'assignedPartner'])->latest();
        if ($admin->city_id) {
            $bookingsQuery->where('city_id', $admin->city_id);
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $this->transformUser($user->loadCount(['bookings' => fn ($q) => $admin->city_id ? $q->where('city_id', $admin->city_id) : $q]), true),
            'bookings' => $bookingsQuery
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
        $this->abortUnlessUserInScope($user);

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
