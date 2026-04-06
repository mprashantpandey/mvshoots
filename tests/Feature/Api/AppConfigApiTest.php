<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_config_endpoint_returns_mobile_safe_dynamic_settings(): void
    {
        Setting::putMany([
            'app_name' => 'VM Shoot Pro',
            'contact_phone' => '9876543210',
            'contact_email' => 'hello@vmshoot.test',
            'privacy_policy_title' => 'Privacy & Data',
            'privacy_policy_content' => 'We only use your data to manage bookings.',
            'user_account_deletion_enabled' => '1',
            'razorpay_enabled' => '1',
            'razorpay_key_id' => 'rzp_test_123',
            'razorpay_key_secret' => 'secret_123',
            'razorpay_merchant_name' => 'MV Shoots',
            'razorpay_logo_url' => 'https://example.com/logo.png',
            'booking_advance_percentage' => '25',
            'global_maintenance_mode' => '1',
            'maintenance_message' => 'Platform maintenance in progress.',
            'user_welcome_dialog_enabled' => '1',
            'user_welcome_dialog_title' => 'Welcome back',
            'user_welcome_dialog_message' => 'Your next shoot is a tap away.',
            'user_welcome_dialog_primary_button_text' => 'Browse',
            'user_welcome_dialog_primary_button_action_type' => 'route',
            'user_welcome_dialog_primary_button_action_value' => '/categories',
            'user_dialog_enabled' => '1',
            'user_dialog_title' => 'User update',
            'user_dialog_message' => 'This appears only in the user app.',
            'user_dialog_image_url' => 'https://example.com/user-dialog.png',
            'user_dialog_primary_button_text' => 'Open Notifications',
            'user_dialog_primary_button_action_type' => 'route',
            'user_dialog_primary_button_action_value' => '/notifications',
            'user_dialog_secondary_button_text' => 'Later',
            'user_dialog_secondary_button_action_type' => 'dismiss',
            'user_dialog_dismissible' => '0',
            'partner_dialog_enabled' => '1',
            'partner_dialog_title' => 'Partner update',
            'partner_dialog_message' => 'This appears only in the partner app.',
            'partner_dialog_primary_button_text' => 'Open Bookings',
            'partner_dialog_primary_button_action_type' => 'route',
            'partner_dialog_primary_button_action_value' => '/',
            'partner_dialog_dismissible' => '1',
            'owner_dialog_enabled' => '0',
            'user_android_latest_version' => '1.2.0',
            'user_android_force_update' => '1',
            'user_android_store_url' => 'https://play.google.com/store/apps/details?id=user.app',
            'mail_from_address' => 'noreply@vmshoot.test',
            'firebase_project_id' => 'vmshoot-firebase',
            'firebase_api_key' => 'firebase-api-key',
            'firebase_app_id' => 'firebase-app-id',
            'firebase_messaging_sender_id' => '1234567890',
            'firebase_storage_bucket' => 'vmshoot.appspot.com',
            'firebase_database_url' => 'https://vmshoot.firebaseio.com',
            'firebase_measurement_id' => 'G-12345',
            'firebase_web_push_key' => 'web-push-key',
            'firebase_service_account_json' => '{"type":"service_account"}',
        ]);

        (new \App\Providers\AppServiceProvider(app()))->boot();

        $response = $this->getJson('/api/v1/app-config?app=user');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.app_name', 'VM Shoot Pro')
            ->assertJsonPath('data.contact.phone', '9876543210')
            ->assertJsonPath('data.contact.email', 'noreply@vmshoot.test')
            ->assertJsonPath('data.privacy_policy.title', 'Privacy & Data')
            ->assertJsonPath('data.privacy_policy.content', 'We only use your data to manage bookings.')
            ->assertJsonPath('data.account.deletion_enabled', true)
            ->assertJsonPath('data.payment_gateway.provider', 'razorpay')
            ->assertJsonPath('data.payment_gateway.razorpay.enabled', true)
            ->assertJsonPath('data.payment_gateway.razorpay.key_id', 'rzp_test_123')
            ->assertJsonPath('data.booking.advance_percentage', 25)
            ->assertJsonPath('data.maintenance.enabled', true)
            ->assertJsonPath('data.versioning.latest_version', '1.2.0')
            ->assertJsonPath('data.versioning.force_update', true)
            ->assertJsonPath('data.welcome_dialog.enabled', true)
            ->assertJsonPath('data.welcome_dialog.title', 'Welcome back')
            ->assertJsonPath('data.welcome_dialog.primary_button.action_value', '/categories')
            ->assertJsonPath('data.custom_dialog.enabled', true)
            ->assertJsonPath('data.custom_dialog.title', 'User update')
            ->assertJsonPath('data.custom_dialog.dismissible', false)
            ->assertJsonPath('data.custom_dialog.primary_button.action_type', 'route')
            ->assertJsonPath('data.custom_dialog.primary_button.action_value', '/notifications')
            ->assertJsonPath('data.firebase.project_id', 'vmshoot-firebase')
            ->assertJsonMissingPath('data.firebase.service_account_json');
    }

    public function test_app_config_returns_partner_specific_dialog(): void
    {
        Setting::putMany([
            'user_dialog_enabled' => '1',
            'user_dialog_title' => 'For users only',
            'user_dialog_message' => 'Visible only in the user app.',
            'partner_dialog_enabled' => '1',
            'partner_dialog_title' => 'For partners only',
            'partner_dialog_message' => 'Visible only in the partner app.',
            'owner_dialog_enabled' => '0',
        ]);

        (new \App\Providers\AppServiceProvider(app()))->boot();

        $this->getJson('/api/v1/app-config?app=partner')
            ->assertOk()
            ->assertJsonPath('data.custom_dialog.enabled', true)
            ->assertJsonPath('data.custom_dialog.title', 'For partners only');
    }

    public function test_app_config_hides_owner_dialog_when_disabled(): void
    {
        Setting::putMany([
            'owner_dialog_enabled' => '0',
            'owner_dialog_title' => 'Owner hidden dialog',
        ]);

        (new \App\Providers\AppServiceProvider(app()))->boot();

        $this->getJson('/api/v1/app-config?app=owner')
            ->assertOk()
            ->assertJsonPath('data.custom_dialog.enabled', false)
            ->assertJsonPath('data.custom_dialog.title', null);
    }

    public function test_app_config_endpoint_requires_valid_app_parameter(): void
    {
        $this->getJson('/api/v1/app-config')
            ->assertStatus(422);

        $this->getJson('/api/v1/app-config?app=admin')
            ->assertStatus(422);
    }
}
