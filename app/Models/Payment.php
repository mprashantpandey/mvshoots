<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_type',
        'amount',
        'payment_status',
        'payment_reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['booking_id'] ?? null, fn ($builder, $bookingId) => $builder->where('booking_id', $bookingId))
            ->when($filters['payment_type'] ?? null, fn ($builder, $type) => $builder->where('payment_type', $type))
            ->when($filters['payment_status'] ?? null, fn ($builder, $status) => $builder->where('payment_status', $status))
            ->when($filters['date'] ?? null, fn ($builder, $date) => $builder->whereDate('paid_at', $date))
            ->when($filters['user'] ?? null, function ($builder, $user): void {
                $builder->whereHas('booking.user', function ($userQuery) use ($user): void {
                    $userQuery->where('name', 'like', "%{$user}%")
                        ->orWhere('phone', 'like', "%{$user}%")
                        ->orWhere('email', 'like', "%{$user}%");
                });
            });
    }
}
