<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\NotificationResource;
use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $actor = $this->resolveActor($request);

        $notifications = AppNotification::query()
            ->where('user_type', $actor['user_type'])
            ->where('user_id', $actor['user_id'])
            ->latest()
            ->paginate(20);

        return $this->success([
            ...NotificationResource::collection($notifications)->response()->getData(true),
            'unread_count' => AppNotification::query()
                ->where('user_type', $actor['user_type'])
                ->where('user_id', $actor['user_id'])
                ->where('is_read', false)
                ->count(),
        ], 'Notifications fetched');
    }

    public function registerDeviceToken(Request $request): JsonResponse
    {
        $actor = $this->resolveActor($request);

        $data = $request->validate([
            'device_token' => ['required', 'string'],
            'platform' => ['nullable', 'string'],
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_type' => $actor['user_type'],
                'user_id' => $actor['user_id'],
                'device_token' => $data['device_token'],
            ],
            ['platform' => $data['platform'] ?? 'unknown']
        );

        return $this->success($deviceToken, 'Device token registered');
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        $actor = $this->resolveActor($request);

        abort_unless(
            $notification->user_type === $actor['user_type'] && (int) $notification->user_id === (int) $actor['user_id'],
            403,
            'You are not authorized to update this notification.'
        );

        $notification->update(['is_read' => true]);

        return $this->success(new NotificationResource($notification->fresh()), 'Notification marked as read');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $actor = $this->resolveActor($request);

        AppNotification::query()
            ->where('user_type', $actor['user_type'])
            ->where('user_id', $actor['user_id'])
            ->update(['is_read' => true]);

        return $this->success(null, 'All notifications marked as read');
    }

    private function resolveActor(Request $request): array
    {
        $actor = $request->user('sanctum');

        return match (true) {
            $actor instanceof User => [
                'user_type' => 'user',
                'user_id' => $actor->id,
            ],
            $actor instanceof Partner => [
                'user_type' => 'partner',
                'user_id' => $actor->id,
            ],
            $actor instanceof Owner => [
                'user_type' => 'owner',
                'user_id' => $actor->id,
            ],
            default => abort(403, 'You are not authorized to access notifications.'),
        };
    }
}
