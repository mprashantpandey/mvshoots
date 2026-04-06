<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\NotificationBroadcastRequest;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'user_type', 'type', 'is_read']);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => AppNotification::query()
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (AppNotification $notification) => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'user_type' => $notification->user_type,
                    'user_id' => $notification->user_id,
                    'target_label' => ucfirst($notification->user_type).' #'.$notification->user_id,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'read_label' => $notification->is_read ? 'Read' : 'Unread',
                    'created_at' => optional($notification->created_at)?->toDateTimeString(),
                ]),
            'filters' => $filters,
            'recipientOptions' => [
                'user' => User::query()->orderBy('name')->get(['id', 'name', 'phone'])->map(fn (User $user) => [
                    'id' => $user->id,
                    'label' => "{$user->name} ({$user->phone})",
                ])->values(),
                'partner' => Partner::query()->orderBy('name')->get(['id', 'name', 'phone'])->map(fn (Partner $partner) => [
                    'id' => $partner->id,
                    'label' => "{$partner->name} ({$partner->phone})",
                ])->values(),
                'owner' => Owner::query()->orderBy('name')->get(['id', 'name', 'email'])->map(fn (Owner $owner) => [
                    'id' => $owner->id,
                    'label' => "{$owner->name} ({$owner->email})",
                ])->values(),
                'admin' => Admin::query()->orderBy('name')->get(['id', 'name', 'email'])->map(fn (Admin $admin) => [
                    'id' => $admin->id,
                    'label' => "{$admin->name} ({$admin->email})",
                ])->values(),
            ],
        ]);
    }

    public function store(NotificationBroadcastRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->notificationService->create(
            $data['user_type'],
            (int) $data['user_id'],
            $data['title'],
            $data['body'],
            $data['type'],
            $data['reference_id'] ?? null
        );

        return redirect()->route('admin.notifications.index')->with('status', 'Notification sent.');
    }

    public function updateReadState(Request $request, AppNotification $notification): RedirectResponse
    {
        $data = $request->validate([
            'is_read' => ['required', 'boolean'],
        ]);

        $notification->update([
            'is_read' => (bool) $data['is_read'],
        ]);

        return redirect()->back()->with('status', 'Notification state updated.');
    }
}
