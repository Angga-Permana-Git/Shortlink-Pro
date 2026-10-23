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
            self::Active => __('Aktif'),
            self::Inactive => __('Nonaktif'),
            self::Expired => __('Kedaluwarsa'),
            self::Scheduled => __('Terjadwal'),
            self::Protected => __('Dilindungi'),
        };
    }
}
