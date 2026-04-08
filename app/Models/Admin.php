<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'city_id', 'is_main'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'city_id' => 'integer',
        'is_main' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Admin $admin): void {
            if ($admin->is_main) {
                static::query()
                    ->when($admin->exists, fn ($q) => $q->whereKeyNot($admin->getKey()))
                    ->update(['is_main' => false]);
            }
        });
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Platform-wide access (catalog, settings, all cities).
     * When {@see $city_id} is set, the admin is limited to that city.
     */
    public function isSuperAdmin(): bool
    {
        return $this->city_id === null;
    }

    /**
     * The single main platform account (creates city admins, full access).
     */
    public function isMainAdmin(): bool
    {
        return $this->is_main === true;
    }
}
