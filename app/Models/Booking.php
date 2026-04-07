<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'category_id',
        'plan_id',
        'assigned_partner_id',
        'booking_date',
        'booking_time',
        'address',
        'notes',
        'status',
        'advance_amount',
        'final_amount',
        'advance_paid',
        'final_paid',
        'total_amount',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'city_id' => 'integer',
        'advance_paid' => 'boolean',
        'final_paid' => 'boolean',
        'advance_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => BookingStatus::Pending->value,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function assignedPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'assigned_partner_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(BookingResult::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function partnerRating(): HasOne
    {
        return $this->hasOne(PartnerRating::class);
    }

    public function latestPayment(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['booking_id'] ?? null, fn ($builder, $bookingId) => $builder->where('id', $bookingId))
            ->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('status', $status))
            ->when($filters['date'] ?? null, fn ($builder, $date) => $builder->whereDate('booking_date', $date))
            ->when($filters['category_id'] ?? null, fn ($builder, $categoryId) => $builder->where('category_id', $categoryId))
            ->when($filters['plan_id'] ?? null, fn ($builder, $planId) => $builder->where('plan_id', $planId))
            ->when($filters['partner_id'] ?? null, fn ($builder, $partnerId) => $builder->where('assigned_partner_id', $partnerId))
            ->when($filters['city_id'] ?? null, fn ($builder, $cityId) => $builder->where('city_id', $cityId))
            ->when($filters['payment_status'] ?? null, function ($builder, $paymentStatus): void {
                $builder->whereHas('payments', fn ($paymentQuery) => $paymentQuery->where('payment_status', $paymentStatus));
            })
            ->when($filters['user'] ?? null, function ($builder, $user): void {
                $builder->whereHas('user', function ($userQuery) use ($user): void {
                    $userQuery->where('name', 'like', "%{$user}%")
                        ->orWhere('phone', 'like', "%{$user}%")
                        ->orWhere('email', 'like', "%{$user}%");
                });
            });
    }
}
