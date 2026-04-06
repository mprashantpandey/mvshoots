<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DeviceToken;

class NotificationService
{
    public function create(string $userType, int $userId, string $title, string $body, string $type, ?int $referenceId = null): AppNotification
    {
        return AppNotification::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);
    }

    public function dispatchToRegisteredDevices(string $userType, int $userId, array $payload): array
    {
        $tokens = DeviceToken::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->pluck('device_token')
            ->all();

        return [
            'tokens' => $tokens,
            'payload' => $payload,
        ];
    }
}
