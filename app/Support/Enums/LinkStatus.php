<?php

namespace App\Support\Enums;

enum LinkStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Scheduled = 'scheduled';
    case Protected = 'protected';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Expired => 'Expired',
            self::Scheduled => 'Scheduled',
            self::Protected => 'Protected',
        };
    }
}