<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected const ENCRYPTED_KEYS = [
        'mail_password',
        'firebase_api_key',
        'firebase_app_id',
        'firebase_service_account_json',
        'firebase_web_push_key',
    ];

    public static function value(string $key, mixed $default = null): mixed
    {
        $value = static::query()->where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        if (in_array($key, self::ENCRYPTED_KEYS, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }

    public static function putMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if ($value === null) {
                static::query()->updateOrCreate(['key' => $key], ['value' => null]);
                continue;
            }

            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => in_array($key, self::ENCRYPTED_KEYS, true) ? Crypt::encryptString((string) $value) : $value]
            );
        }
    }

    public static function allAsKeyValue(): array
    {
        return static::query()
            ->get(['key', 'value'])
            ->mapWithKeys(fn (self $setting) => [$setting->key => static::value($setting->key)])
            ->all();
    }
}
