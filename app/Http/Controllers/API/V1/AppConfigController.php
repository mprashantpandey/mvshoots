<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppConfigController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'app' => ['required', Rule::in(['user', 'partner', 'owner'])],
        ]);

        $appKey = "{$data['app']}_app";
        $mobileConfig = config("platform.mobile.{$appKey}", []);
        $dialogConfig = $mobileConfig['custom_dialog'] ?? [];

        return $this->success([
            'app_name' => config('app.name'),
            'contact' => [
                'email' => config('mail.from.address'),
                'phone' => \App\Models\Setting::value('contact_phone'),
            ],
            'booking' => [
                'advance_percentage' => config('platform.mobile.booking_advance_percentage', 20),
            ],
            'maintenance' => [
                'enabled' => config('platform.mobile.global_maintenance_mode', false),
                'message' => config('platform.mobile.maintenance_message'),
            ],
            'versioning' => [
                'latest_version' => $mobileConfig['android_latest_version'] ?? null,
                'force_update' => $mobileConfig['android_force_update'] ?? false,
                'store_url' => $mobileConfig['android_store_url'] ?? null,
            ],
            'custom_dialog' => [
                'enabled' => $dialogConfig['enabled'] ?? false,
                'dismissible' => $dialogConfig['dismissible'] ?? true,
                'title' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['title'] ?? null) : null,
                'message' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['message'] ?? null) : null,
                'image_url' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['image_url'] ?? null) : null,
                'primary_button' => [
                    'text' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['primary_button']['text'] ?? 'OK') : null,
                    'action_type' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['primary_button']['action_type'] ?? 'dismiss') : null,
                    'action_value' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['primary_button']['action_value'] ?? null) : null,
                ],
                'secondary_button' => [
                    'text' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['secondary_button']['text'] ?? null) : null,
                    'action_type' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['secondary_button']['action_type'] ?? null) : null,
                    'action_value' => ($dialogConfig['enabled'] ?? false) ? ($dialogConfig['secondary_button']['action_value'] ?? null) : null,
                ],
            ],
            'firebase' => [
                'project_id' => config('services.firebase.project_id'),
                'api_key' => config('services.firebase.api_key'),
                'app_id' => config('services.firebase.app_id'),
                'messaging_sender_id' => config('services.firebase.messaging_sender_id'),
                'storage_bucket' => config('services.firebase.storage_bucket'),
                'database_url' => config('services.firebase.database_url'),
                'measurement_id' => config('services.firebase.measurement_id'),
                'web_push_key' => config('services.firebase.web_push_key'),
            ],
        ], 'App config fetched');
    }
}
