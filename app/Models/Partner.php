<?php

namespace App\Models;

use App\Enums\PartnerKycStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Partner extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = ['name', 'phone', 'email', 'city_id', 'firebase_uid', 'status'];

    protected $hidden = ['remember_token'];

    protected $casts = [
        'city_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Partner $partner): void {
            $partner->kyc?->delete();
        });
    }

    public function managedCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function serviceCities(): BelongsToMany
    {
        return $this->belongsToMany(City::class)->withTimestamps();
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(PartnerKyc::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(PartnerRating::class);
    }

    public function assignedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'assigned_partner_id');
    }

    public function bookingResults(): HasMany
    {
        return $this->hasMany(BookingResult::class, 'uploaded_by_partner_id');
    }

    public function scopeServingCity($query, ?int $cityId)
    {
        if ($cityId === null) {
            return $query;
        }

        return $query->where(function ($eligible) use ($cityId): void {
            $eligible->whereHas('serviceCities', fn ($cityQuery) => $cityQuery->where('cities.id', $cityId))
                ->orWhere(function ($fallback) use ($cityId): void {
                    $fallback->whereDoesntHave('serviceCities')
                        ->where('city_id', $cityId);
                });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['search'] ?? null, function ($builder, $search): void {
                $builder->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['city_id'] ?? null, fn ($builder, $cityId) => $builder->servingCity((int) $cityId))
            ->when($filters['kyc_status'] ?? null, fn ($builder, $kycStatus) => $builder->kycStatus($kycStatus))
            ->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('status', $status));
    }

    public function servesCity(?int $cityId): bool
    {
        if ($cityId === null) {
            return true;
        }

        $serviceCities = $this->relationLoaded('serviceCities')
            ? $this->serviceCities
            : $this->serviceCities()->get(['cities.id']);

        if ($serviceCities->isNotEmpty()) {
            return $serviceCities->contains('id', $cityId);
        }

        return $this->city_id !== null && (int) $this->city_id === (int) $cityId;
    }

    public function hasVerifiedKyc(): bool
    {
        return $this->kyc?->status === PartnerKycStatus::Verified;
    }

    public function canAcceptServiceBookings(): bool
    {
        return $this->status === 'active' && $this->hasVerifiedKyc();
    }

    public function scopeKycVerified($query)
    {
        return $query->whereHas('kyc', fn ($q) => $q->where('status', PartnerKycStatus::Verified->value));
    }

    public function scopeKycStatus($query, ?string $status)
    {
        if ($status === null || $status === '') {
            return $query;
        }

        if ($status === 'not_submitted') {
            return $query->whereDoesntHave('kyc');
        }

        return $query->whereHas('kyc', fn ($q) => $q->where('status', $status));
    }
}
