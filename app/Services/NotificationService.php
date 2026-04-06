<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function create(string $userType, int $userId, string $title, string $body, string $type, ?int $referenceId = null): AppNotification
    {
        $notification = AppNotification::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);

        $this->dispatchToRegisteredDevices($userType, $userId, [
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'notification_id' => (int) $notification->id,
        ]);

        return $notification;
    }

    public function dispatchToRegisteredDevices(string $userType, int $userId, array $payload): array
    {
        $tokens = DeviceToken::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->pluck('device_token')
            ->all();

        if ($tokens === []) {
            return [
                'tokens' => [],
                'payload' => $payload,
                'sent' => 0,
                'provider' => 'none',
            ];
        }

        $serviceAccountJson = config('services.firebase.service_account_json');
        $serviceAccount = is_string($serviceAccountJson) && $serviceAccountJson !== ''
            ? json_decode($serviceAccountJson, true)
            : null;
        $projectId = (string) (config('services.firebase.project_id') ?: ($serviceAccount['project_id'] ?? ''));

        if (! is_array($serviceAccount) || $projectId === '') {
            Log::info('FCM push skipped because Firebase server credentials are missing.', [
                'user_type' => $userType,
                'user_id' => $userId,
                'token_count' => count($tokens),
            ]);

            return [
                'tokens' => $tokens,
                'payload' => $payload,
                'sent' => 0,
                'provider' => 'fcm',
                'skipped' => 'missing_credentials',
            ];
        }

        $accessToken = $this->getFirebaseAccessToken($serviceAccount);
        if ($accessToken === null) {
            return [
                'tokens' => $tokens,
                'payload' => $payload,
                'sent' => 0,
                'provider' => 'fcm',
                'skipped' => 'token_generation_failed',
            ];
        }

        $endpoint = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $projectId
        );

        $data = collect([
            'title' => (string) ($payload['title'] ?? ''),
            'body' => (string) ($payload['body'] ?? ''),
            'type' => (string) ($payload['type'] ?? ''),
            'reference_id' => isset($payload['reference_id']) ? (string) $payload['reference_id'] : null,
            'notification_id' => isset($payload['notification_id']) ? (string) $payload['notification_id'] : null,
        ])->filter(fn ($value) => $value !== null && $value !== '')->all();

        $sent = 0;

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($endpoint, [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => (string) ($payload['title'] ?? 'MV Shoots'),
                            'body' => (string) ($payload['body'] ?? ''),
                        ],
                        'data' => $data,
                        'android' => [
                            'priority' => 'high',
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $sent++;
                continue;
            }

            $body = $response->json();
            $message = strtolower((string) data_get($body, 'error.message', ''));
            $status = strtolower((string) data_get($body, 'error.status', ''));

            if (str_contains($message, 'unregistered') || str_contains($status, 'unregistered')) {
                DeviceToken::query()->where('device_token', $token)->delete();
            }

            Log::warning('FCM push failed.', [
                'user_type' => $userType,
                'user_id' => $userId,
                'token' => $token,
                'status_code' => $response->status(),
                'response' => $body,
            ]);
        }

        return [
            'tokens' => $tokens,
            'payload' => $payload,
            'sent' => $sent,
            'provider' => 'fcm',
        ];
    }

    private function getFirebaseAccessToken(array $serviceAccount): ?string
    {
        $clientEmail = $serviceAccount['client_email'] ?? null;
        $privateKey = $serviceAccount['private_key'] ?? null;
        $tokenUri = $serviceAccount['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        if (! is_string($clientEmail) || $clientEmail === '' || ! is_string($privateKey) || $privateKey === '') {
            Log::warning('FCM push skipped because Firebase service account JSON is incomplete.');

            return null;
        }

        $cacheKey = 'firebase_access_token_'.md5($clientEmail);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($clientEmail, $privateKey, $tokenUri) {
            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ], JSON_THROW_ON_ERROR));

            $issuedAt = time();
            $payload = $this->base64UrlEncode(json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $tokenUri,
                'iat' => $issuedAt,
                'exp' => $issuedAt + 3600,
            ], JSON_THROW_ON_ERROR));

            $signatureInput = $header.'.'.$payload;
            $signature = '';
            $signed = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            if (! $signed) {
                Log::warning('Failed to sign Firebase OAuth JWT.');

                return null;
            }

            $assertion = $signatureInput.'.'.$this->base64UrlEncode($signature);

            $response = Http::asForm()->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if (! $response->successful()) {
                Log::warning('Failed to exchange Firebase OAuth JWT for access token.', [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
