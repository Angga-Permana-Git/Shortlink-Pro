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
        return match ($this) {
            self::Success => __('Berhasil'),
            self::Expired => __('Kedaluwarsa'),
            self::Inactive => __('Nonaktif'),
            self::Protected => __('Terlindungi'),
            self::Scheduled => __('Terjadwal'),
            self::NotFound => __('Tidak Ditemukan'),
            self::WrongPassword => __('Kata Sandi Salah'),
        };
    }

    public function redirects(): bool
    {
        return $this === self::Success;
    }
}
