<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;

final class AdminCityScope
{
    public static function bookings(Builder $query, ?Admin $admin): Builder
    {
        if ($admin && $admin->city_id) {
            $query->where('city_id', $admin->city_id);
        }

        return $query;
    }

    public static function partners(Builder $query, ?Admin $admin): Builder
    {
        if ($admin && $admin->city_id) {
            $query->servingCity((int) $admin->city_id);
        }

        return $query;
    }

    public static function payments(Builder $query, ?Admin $admin): Builder
    {
        if ($admin && $admin->city_id) {
            $query->whereHas('booking', fn (Builder $b) => $b->where('city_id', $admin->city_id));
        }

        return $query;
    }

    public static function users(Builder $query, ?Admin $admin): Builder
    {
        if ($admin && $admin->city_id) {
            $query->whereHas('bookings', fn (Builder $b) => $b->where('city_id', $admin->city_id));
        }

        return $query;
    }

    public static function partnerKycPendingBase(Builder $query, ?Admin $admin): Builder
    {
        return self::partners($query, $admin);
    }

    public static function adminCanAccessBooking(?Admin $admin, Booking $booking): bool
    {
        if (! $admin || $admin->isSuperAdmin()) {
            return true;
        }

        return $admin->city_id && (int) $booking->city_id === (int) $admin->city_id;
    }

    public static function adminCanAccessPartner(?Admin $admin, Partner $partner): bool
    {
        if (! $admin || $admin->isSuperAdmin()) {
            return true;
        }

        return $admin->city_id && $partner->servesCity((int) $admin->city_id);
    }
}
