<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'booking_advance_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'global_maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'user_dialog_enabled' => ['nullable', 'boolean'],
            'user_dialog_title' => ['nullable', 'string', 'max:120'],
            'user_dialog_message' => ['nullable', 'string', 'max:1000'],
            'user_dialog_image_url' => ['nullable', 'url'],
            'user_dialog_primary_button_text' => ['nullable', 'string', 'max:40'],
            'user_dialog_primary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'user_dialog_primary_button_action_value' => ['nullable', 'string', 'max:500'],
            'user_dialog_secondary_button_text' => ['nullable', 'string', 'max:40'],
            'user_dialog_secondary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'user_dialog_secondary_button_action_value' => ['nullable', 'string', 'max:500'],
            'user_dialog_dismissible' => ['nullable', 'boolean'],
            'partner_dialog_enabled' => ['nullable', 'boolean'],
            'partner_dialog_title' => ['nullable', 'string', 'max:120'],
            'partner_dialog_message' => ['nullable', 'string', 'max:1000'],
            'partner_dialog_image_url' => ['nullable', 'url'],
            'partner_dialog_primary_button_text' => ['nullable', 'string', 'max:40'],
            'partner_dialog_primary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'partner_dialog_primary_button_action_value' => ['nullable', 'string', 'max:500'],
            'partner_dialog_secondary_button_text' => ['nullable', 'string', 'max:40'],
            'partner_dialog_secondary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'partner_dialog_secondary_button_action_value' => ['nullable', 'string', 'max:500'],
            'partner_dialog_dismissible' => ['nullable', 'boolean'],
            'owner_dialog_enabled' => ['nullable', 'boolean'],
            'owner_dialog_title' => ['nullable', 'string', 'max:120'],
            'owner_dialog_message' => ['nullable', 'string', 'max:1000'],
            'owner_dialog_image_url' => ['nullable', 'url'],
            'owner_dialog_primary_button_text' => ['nullable', 'string', 'max:40'],
            'owner_dialog_primary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'owner_dialog_primary_button_action_value' => ['nullable', 'string', 'max:500'],
            'owner_dialog_secondary_button_text' => ['nullable', 'string', 'max:40'],
            'owner_dialog_secondary_button_action_type' => ['nullable', 'in:none,dismiss,url,route'],
            'owner_dialog_secondary_button_action_value' => ['nullable', 'string', 'max:500'],
            'owner_dialog_dismissible' => ['nullable', 'boolean'],
            'user_android_latest_version' => ['nullable', 'string', 'max:50'],
            'user_android_force_update' => ['nullable', 'boolean'],
            'user_android_store_url' => ['nullable', 'url'],
            'partner_android_latest_version' => ['nullable', 'string', 'max:50'],
            'partner_android_force_update' => ['nullable', 'boolean'],
            'partner_android_store_url' => ['nullable', 'url'],
            'owner_android_latest_version' => ['nullable', 'string', 'max:50'],
            'owner_android_force_update' => ['nullable', 'boolean'],
            'owner_android_store_url' => ['nullable', 'url'],
            'mail_mailer' => ['required', 'in:smtp,log,sendmail,array'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string'],
            'mail_encryption' => ['nullable', 'in:tls,ssl'],
            'mail_from_address' => ['nullable', 'email'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'firebase_project_id' => ['nullable', 'string', 'max:255'],
            'firebase_api_key' => ['nullable', 'string'],
            'firebase_app_id' => ['nullable', 'string'],
            'firebase_messaging_sender_id' => ['nullable', 'string', 'max:255'],
            'firebase_storage_bucket' => ['nullable', 'string', 'max:255'],
            'firebase_database_url' => ['nullable', 'url'],
            'firebase_measurement_id' => ['nullable', 'string', 'max:255'],
            'firebase_web_push_key' => ['nullable', 'string'],
            'firebase_service_account_json' => ['nullable', 'string'],
            'branding_logo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
