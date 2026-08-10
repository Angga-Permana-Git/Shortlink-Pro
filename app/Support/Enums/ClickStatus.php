<?php

namespace App\Support\Enums;

enum ClickStatus: string
{
    case Success = 'success';
    case Expired = 'expired';
    case Inactive = 'inactive';
    case Protected = 'protected';
    case Scheduled = 'scheduled';
    case NotFound = 'not_found';
    case WrongPassword = 'wrong_password';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->headline();
    }

    public function redirects(): bool
    {
        return $this === self::Success;
    }
}