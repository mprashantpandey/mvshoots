<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
