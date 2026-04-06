<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateways\RazorpayPaymentGateway;
use App\Services\PaymentGateways\StubPaymentGateway;
use Throwable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            if (config('services.razorpay.enabled')
                && filled(config('services.razorpay.key_id'))
                && filled(config('services.razorpay.key_secret'))) {
                return $app->make(RazorpayPaymentGateway::class);
            }

            return $app->make(StubPaymentGateway::class);
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        $settings = Setting::allAsKeyValue();
        $appName = $settings['app_name'] ?? config('app.name');
        $mailMailer = $settings['mail_mailer'] ?? config('mail.default');
        $mailHost = $settings['mail_host'] ?? config('mail.mailers.smtp.host');
        $mailPort = $settings['mail_port'] ?? config('mail.mailers.smtp.port');
        $mailUsername = $settings['mail_username'] ?? config('mail.mailers.smtp.username');
        $mailPassword = $settings['mail_password'] ?? config('mail.mailers.smtp.password');
        $mailEncryption = $settings['mail_encryption'] ?? config('mail.mailers.smtp.scheme');
        $mailFromAddress = $settings['mail_from_address'] ?? config('mail.from.address');
        $mailFromName = $settings['mail_from_name'] ?? $appName;

        config([
            'app.name' => $appName,
            'mail.default' => $mailMailer,
            'mail.mailers.smtp.host' => $mailHost,
            'mail.mailers.smtp.port' => (int) $mailPort,
            'mail.mailers.smtp.username' => $mailUsername,
            'mail.mailers.smtp.password' => $mailPassword,
            'mail.mailers.smtp.scheme' => $mailEncryption,
            'mail.from.address' => $mailFromAddress,
            'mail.from.name' => $mailFromName,
            'services.firebase' => [
                'project_id' => $settings['firebase_project_id'] ?? null,
                'api_key' => $settings['firebase_api_key'] ?? null,
                'app_id' => $settings['firebase_app_id'] ?? null,
                'messaging_sender_id' => $settings['firebase_messaging_sender_id'] ?? null,
                'storage_bucket' => $settings['firebase_storage_bucket'] ?? null,
                'database_url' => $settings['firebase_database_url'] ?? null,
                'measurement_id' => $settings['firebase_measurement_id'] ?? null,
                'web_push_key' => $settings['firebase_web_push_key'] ?? null,
                'service_account_json' => $settings['firebase_service_account_json'] ?? null,
            ],
            'services.razorpay' => [
                'enabled' => filter_var($settings['razorpay_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                'key_id' => $settings['razorpay_key_id'] ?? null,
                'key_secret' => $settings['razorpay_key_secret'] ?? null,
                'webhook_secret' => $settings['razorpay_webhook_secret'] ?? null,
                'merchant_name' => $settings['razorpay_merchant_name'] ?? $appName,
                'logo_url' => $settings['razorpay_logo_url'] ?? null,
            ],
            'platform.mobile' => [
                'booking_advance_percentage' => (float) ($settings['booking_advance_percentage'] ?? 20),
                'global_maintenance_mode' => filter_var($settings['global_maintenance_mode'] ?? false, FILTER_VALIDATE_BOOL),
                'maintenance_message' => $settings['maintenance_message'] ?? null,
                'privacy_policy' => [
                    'title' => $settings['privacy_policy_title'] ?? 'Privacy Policy',
                    'content' => $settings['privacy_policy_content'] ?? null,
                ],
                'user_account_deletion_enabled' => filter_var($settings['user_account_deletion_enabled'] ?? true, FILTER_VALIDATE_BOOL),
                'payment_gateway' => [
                    'provider' => filter_var($settings['razorpay_enabled'] ?? false, FILTER_VALIDATE_BOOL) ? 'razorpay' : 'stub',
                    'razorpay' => [
                        'enabled' => filter_var($settings['razorpay_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                        'key_id' => $settings['razorpay_key_id'] ?? null,
                        'merchant_name' => $settings['razorpay_merchant_name'] ?? $appName,
                        'logo_url' => $settings['razorpay_logo_url'] ?? null,
                    ],
                ],
                'user_app' => [
                    'android_latest_version' => $settings['user_android_latest_version'] ?? null,
                    'android_force_update' => filter_var($settings['user_android_force_update'] ?? false, FILTER_VALIDATE_BOOL),
                    'android_store_url' => $settings['user_android_store_url'] ?? null,
                    'welcome_dialog' => [
                        'enabled' => filter_var($settings['user_welcome_dialog_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                        'dismissible' => filter_var($settings['user_welcome_dialog_dismissible'] ?? true, FILTER_VALIDATE_BOOL),
                        'title' => $settings['user_welcome_dialog_title'] ?? null,
                        'message' => $settings['user_welcome_dialog_message'] ?? null,
                        'image_url' => $settings['user_welcome_dialog_image_url'] ?? null,
                        'primary_button' => [
                            'text' => $settings['user_welcome_dialog_primary_button_text'] ?? 'Continue',
                            'action_type' => $settings['user_welcome_dialog_primary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['user_welcome_dialog_primary_button_action_value'] ?? null,
                        ],
                        'secondary_button' => [
                            'text' => $settings['user_welcome_dialog_secondary_button_text'] ?? null,
                            'action_type' => $settings['user_welcome_dialog_secondary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['user_welcome_dialog_secondary_button_action_value'] ?? null,
                        ],
                    ],
                    'custom_dialog' => [
                        'enabled' => filter_var($settings['user_dialog_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                        'dismissible' => filter_var($settings['user_dialog_dismissible'] ?? true, FILTER_VALIDATE_BOOL),
                        'title' => $settings['user_dialog_title'] ?? null,
                        'message' => $settings['user_dialog_message'] ?? null,
                        'image_url' => $settings['user_dialog_image_url'] ?? null,
                        'primary_button' => [
                            'text' => $settings['user_dialog_primary_button_text'] ?? 'OK',
                            'action_type' => $settings['user_dialog_primary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['user_dialog_primary_button_action_value'] ?? null,
                        ],
                        'secondary_button' => [
                            'text' => $settings['user_dialog_secondary_button_text'] ?? null,
                            'action_type' => $settings['user_dialog_secondary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['user_dialog_secondary_button_action_value'] ?? null,
                        ],
                    ],
                ],
                'partner_app' => [
                    'android_latest_version' => $settings['partner_android_latest_version'] ?? null,
                    'android_force_update' => filter_var($settings['partner_android_force_update'] ?? false, FILTER_VALIDATE_BOOL),
                    'android_store_url' => $settings['partner_android_store_url'] ?? null,
                    'custom_dialog' => [
                        'enabled' => filter_var($settings['partner_dialog_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                        'dismissible' => filter_var($settings['partner_dialog_dismissible'] ?? true, FILTER_VALIDATE_BOOL),
                        'title' => $settings['partner_dialog_title'] ?? null,
                        'message' => $settings['partner_dialog_message'] ?? null,
                        'image_url' => $settings['partner_dialog_image_url'] ?? null,
                        'primary_button' => [
                            'text' => $settings['partner_dialog_primary_button_text'] ?? 'OK',
                            'action_type' => $settings['partner_dialog_primary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['partner_dialog_primary_button_action_value'] ?? null,
                        ],
                        'secondary_button' => [
                            'text' => $settings['partner_dialog_secondary_button_text'] ?? null,
                            'action_type' => $settings['partner_dialog_secondary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['partner_dialog_secondary_button_action_value'] ?? null,
                        ],
                    ],
                ],
                'owner_app' => [
                    'android_latest_version' => $settings['owner_android_latest_version'] ?? null,
                    'android_force_update' => filter_var($settings['owner_android_force_update'] ?? false, FILTER_VALIDATE_BOOL),
                    'android_store_url' => $settings['owner_android_store_url'] ?? null,
                    'custom_dialog' => [
                        'enabled' => filter_var($settings['owner_dialog_enabled'] ?? false, FILTER_VALIDATE_BOOL),
                        'dismissible' => filter_var($settings['owner_dialog_dismissible'] ?? true, FILTER_VALIDATE_BOOL),
                        'title' => $settings['owner_dialog_title'] ?? null,
                        'message' => $settings['owner_dialog_message'] ?? null,
                        'image_url' => $settings['owner_dialog_image_url'] ?? null,
                        'primary_button' => [
                            'text' => $settings['owner_dialog_primary_button_text'] ?? 'OK',
                            'action_type' => $settings['owner_dialog_primary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['owner_dialog_primary_button_action_value'] ?? null,
                        ],
                        'secondary_button' => [
                            'text' => $settings['owner_dialog_secondary_button_text'] ?? null,
                            'action_type' => $settings['owner_dialog_secondary_button_action_type'] ?? 'dismiss',
                            'action_value' => $settings['owner_dialog_secondary_button_action_value'] ?? null,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
