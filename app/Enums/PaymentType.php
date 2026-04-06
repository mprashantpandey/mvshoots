<?php

namespace App\Enums;

enum PaymentType: string
{
    case Advance = 'advance';
    case Final = 'final';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
