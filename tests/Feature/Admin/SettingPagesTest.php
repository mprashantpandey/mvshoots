<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_settings(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@vmshoot.test',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.settings.edit'))
            ->assertOk();

        $response = $this->put(route('admin.settings.update'), [
            'app_name' => 'VM Shoot Pro',
            'contact_email' => 'hello@vmshoot.test',
            'contact_phone' => '9876543210',
            'booking_advance_percentage' => 25,
            'global_maintenance_mode' => true,
            'maintenance_message' => 'Maintenance in progress.',
            'user_dialog_enabled' => true,
            'user_dialog_title' => 'Important Notice',
            'user_dialog_message' => 'Bookings will open at 10 AM tomorrow.',
            'user_dialog_image_url' => 'https://example.com/user-dialog.png',
            'user_dialog_primary_button_text' => 'View Notifications',
            'user_dialog_primary_button_action_type' => 'route',
            'user_dialog_primary_button_action_value' => '/notifications',
            'user_dialog_secondary_button_text' => 'Dismiss',
            'user_dialog_secondary_button_action_type' => 'dismiss',
            'user_dialog_secondary_button_action_value' => null,
            'user_dialog_dismissible' => false,
            'partner_dialog_enabled' => true,
            'partner_dialog_title' => 'Partner Alert',
            'partner_dialog_message' => 'Partner schedules are updated.',
            'partner_dialog_image_url' => 'https://example.com/partner-dialog.png',
            'partner_dialog_primary_button_text' => 'Open Bookings',
            'partner_dialog_primary_button_action_type' => 'route',
            'partner_dialog_primary_button_action_value' => '/',
            'partner_dialog_secondary_button_text' => 'Later',
            'partner_dialog_secondary_button_action_type' => 'dismiss',
            'partner_dialog_secondary_button_action_value' => null,
            'partner_dialog_dismissible' => true,
            'owner_dialog_enabled' => false,
            'owner_dialog_title' => 'Owner Only',
            'owner_dialog_message' => 'Owner dialog copy.',
            'owner_dialog_image_url' => 'https://example.com/owner-dialog.png',
            'owner_dialog_primary_button_text' => 'Open Dashboard',
            'owner_dialog_primary_button_action_type' => 'route',
            'owner_dialog_primary_button_action_value' => '/',
            'owner_dialog_secondary_button_text' => 'Later',
            'owner_dialog_secondary_button_action_type' => 'dismiss',
            'owner_dialog_secondary_button_action_value' => null,
            'owner_dialog_dismissible' => true,
            'user_android_latest_version' => '1.2.0',
            'user_android_force_update' => true,
            'user_android_store_url' => 'https://play.google.com/store/apps/details?id=user.app',
            'partner_android_latest_version' => '1.4.0',
            'partner_android_force_update' => false,
            'partner_android_store_url' => 'https://play.google.com/store/apps/details?id=partner.app',
            'owner_android_latest_version' => '1.1.0',
            'owner_android_force_update' => true,
            'owner_android_store_url' => 'https://example.com/owner.apk',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'smtp-user',
            'mail_password' => 'smtp-secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@vmshoot.test',
            'mail_from_name' => 'VM Shoot Mail',
            'firebase_project_id' => 'vmshoot-firebase',
            'firebase_api_key' => 'firebase-api-key',
            'firebase_app_id' => 'firebase-app-id',
            'firebase_messaging_sender_id' => '1234567890',
            'firebase_storage_bucket' => 'vmshoot.appspot.com',
            'firebase_database_url' => 'https://vmshoot.firebaseio.com',
            'firebase_measurement_id' => 'G-12345',
            'firebase_web_push_key' => 'web-push-key',
            'firebase_service_account_json' => '{"type":"service_account"}',
            'branding_logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('VM Shoot Pro', Setting::value('app_name'));
        $this->assertSame('hello@vmshoot.test', Setting::value('contact_email'));
        $this->assertSame('9876543210', Setting::value('contact_phone'));
        $this->assertSame('25', Setting::value('booking_advance_percentage'));
        $this->assertSame('1', Setting::value('global_maintenance_mode'));
        $this->assertSame('1', Setting::value('user_dialog_enabled'));
        $this->assertSame('Important Notice', Setting::value('user_dialog_title'));
        $this->assertSame('/notifications', Setting::value('user_dialog_primary_button_action_value'));
        $this->assertSame('0', Setting::value('user_dialog_dismissible'));
        $this->assertSame('Partner Alert', Setting::value('partner_dialog_title'));
        $this->assertSame('0', Setting::value('owner_dialog_enabled'));
        $this->assertSame('smtp', Setting::value('mail_mailer'));
        $this->assertSame('smtp.mailtrap.io', Setting::value('mail_host'));
        $this->assertSame('smtp-secret', Setting::value('mail_password'));
        $this->assertSame('vmshoot-firebase', Setting::value('firebase_project_id'));
        $this->assertSame('firebase-api-key', Setting::value('firebase_api_key'));
        $this->assertNotNull(Setting::value('branding_logo'));
        Storage::disk('public')->assertExists(Setting::value('branding_logo'));

        (new \App\Providers\AppServiceProvider(app()))->boot();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.mailtrap.io', config('mail.mailers.smtp.host'));
        $this->assertSame('noreply@vmshoot.test', config('mail.from.address'));
        $this->assertSame('vmshoot-firebase', config('services.firebase.project_id'));
        $this->assertTrue(config('platform.mobile.global_maintenance_mode'));
        $this->assertTrue(config('platform.mobile.user_app.custom_dialog.enabled'));
        $this->assertFalse(config('platform.mobile.user_app.custom_dialog.dismissible'));
        $this->assertTrue(config('platform.mobile.partner_app.custom_dialog.enabled'));
        $this->assertFalse(config('platform.mobile.owner_app.custom_dialog.enabled'));
    }
}
