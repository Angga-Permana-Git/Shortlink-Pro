<?php

namespace App\Support\Enums;

enum Role: string
{
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'User',
        };
    }

    public static function options(): array
    {
        return [
            self::Admin->value => self::Admin->label(),
            self::User->value => self::User->label(),
        ];
    }
}