<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController
{
    private const DEFAULTS = [
        'app_name' => 'VM Shoot',
        'contact_email' => null,
        'contact_phone' => null,
        'booking_advance_percentage' => '20',
        'global_maintenance_mode' => '0',
        'maintenance_message' => null,
        'user_dialog_enabled' => '0',
        'user_dialog_title' => null,
        'user_dialog_message' => null,
        'user_dialog_image_url' => null,
        'user_dialog_primary_button_text' => 'OK',
        'user_dialog_primary_button_action_type' => 'dismiss',
        'user_dialog_primary_button_action_value' => null,
        'user_dialog_secondary_button_text' => null,
        'user_dialog_secondary_button_action_type' => 'dismiss',
        'user_dialog_secondary_button_action_value' => null,
        'user_dialog_dismissible' => '1',
        'partner_dialog_enabled' => '0',
        'partner_dialog_title' => null,
        'partner_dialog_message' => null,
        'partner_dialog_image_url' => null,
        'partner_dialog_primary_button_text' => 'OK',
        'partner_dialog_primary_button_action_type' => 'dismiss',
        'partner_dialog_primary_button_action_value' => null,
        'partner_dialog_secondary_button_text' => null,
        'partner_dialog_secondary_button_action_type' => 'dismiss',
        'partner_dialog_secondary_button_action_value' => null,
        'partner_dialog_dismissible' => '1',
        'owner_dialog_enabled' => '0',
        'owner_dialog_title' => null,
        'owner_dialog_message' => null,
        'owner_dialog_image_url' => null,
        'owner_dialog_primary_button_text' => 'OK',
        'owner_dialog_primary_button_action_type' => 'dismiss',
        'owner_dialog_primary_button_action_value' => null,
        'owner_dialog_secondary_button_text' => null,
        'owner_dialog_secondary_button_action_type' => 'dismiss',
        'owner_dialog_secondary_button_action_value' => null,
        'owner_dialog_dismissible' => '1',
        'user_android_latest_version' => null,
        'user_android_force_update' => '0',
        'user_android_store_url' => null,
        'partner_android_latest_version' => null,
        'partner_android_force_update' => '0',
        'partner_android_store_url' => null,
        'owner_android_latest_version' => null,
        'owner_android_force_update' => '0',
        'owner_android_store_url' => null,
        'mail_mailer' => 'log',
        'mail_host' => null,
        'mail_port' => '2525',
        'mail_username' => null,
        'mail_password' => null,
        'mail_encryption' => null,
        'mail_from_address' => null,
        'mail_from_name' => null,
        'firebase_project_id' => null,
        'firebase_api_key' => null,
        'firebase_app_id' => null,
        'firebase_messaging_sender_id' => null,
        'firebase_storage_bucket' => null,
        'firebase_database_url' => null,
        'firebase_measurement_id' => null,
        'firebase_web_push_key' => null,
        'firebase_service_account_json' => null,
    ];

    public function __construct(private readonly MediaUploadService $mediaUploadService)
    {
    }

    public function edit(): Response
    {
        $brandingLogo = Setting::value('branding_logo');
        $settings = [];

        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = Setting::value($key, $default);
        }

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                ...$settings,
                'global_maintenance_mode' => filter_var($settings['global_maintenance_mode'], FILTER_VALIDATE_BOOL),
                'user_dialog_enabled' => filter_var($settings['user_dialog_enabled'], FILTER_VALIDATE_BOOL),
                'user_dialog_dismissible' => filter_var($settings['user_dialog_dismissible'], FILTER_VALIDATE_BOOL),
                'partner_dialog_enabled' => filter_var($settings['partner_dialog_enabled'], FILTER_VALIDATE_BOOL),
                'partner_dialog_dismissible' => filter_var($settings['partner_dialog_dismissible'], FILTER_VALIDATE_BOOL),
                'owner_dialog_enabled' => filter_var($settings['owner_dialog_enabled'], FILTER_VALIDATE_BOOL),
                'owner_dialog_dismissible' => filter_var($settings['owner_dialog_dismissible'], FILTER_VALIDATE_BOOL),
                'user_android_force_update' => filter_var($settings['user_android_force_update'], FILTER_VALIDATE_BOOL),
                'partner_android_force_update' => filter_var($settings['partner_android_force_update'], FILTER_VALIDATE_BOOL),
                'owner_android_force_update' => filter_var($settings['owner_android_force_update'], FILTER_VALIDATE_BOOL),
                'branding_logo' => $brandingLogo ? asset('storage/'.$brandingLogo) : null,
            ],
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $logo = $this->mediaUploadService->upload($request->file('branding_logo'), 'settings');
        $data['branding_logo'] = $logo ?? Setting::value('branding_logo');
        $data['global_maintenance_mode'] = (string) (int) $request->boolean('global_maintenance_mode');
        $data['user_dialog_enabled'] = (string) (int) $request->boolean('user_dialog_enabled');
        $data['user_dialog_dismissible'] = (string) (int) $request->boolean('user_dialog_dismissible');
        $data['partner_dialog_enabled'] = (string) (int) $request->boolean('partner_dialog_enabled');
        $data['partner_dialog_dismissible'] = (string) (int) $request->boolean('partner_dialog_dismissible');
        $data['owner_dialog_enabled'] = (string) (int) $request->boolean('owner_dialog_enabled');
        $data['owner_dialog_dismissible'] = (string) (int) $request->boolean('owner_dialog_dismissible');
        $data['user_android_force_update'] = (string) (int) $request->boolean('user_android_force_update');
        $data['partner_android_force_update'] = (string) (int) $request->boolean('partner_android_force_update');
        $data['owner_android_force_update'] = (string) (int) $request->boolean('owner_android_force_update');
        $data['mail_port'] = $data['mail_port'] ? (string) $data['mail_port'] : null;
        $data['booking_advance_percentage'] = (string) $data['booking_advance_percentage'];

        Setting::putMany($data);

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }
}
