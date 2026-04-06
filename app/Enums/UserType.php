<?php

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case User = 'user';
    case Partner = 'partner';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
