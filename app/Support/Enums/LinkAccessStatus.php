<?php

namespace App\Support\Enums;

/**
 * Hasil pemeriksaan akses sebuah tautan pendek.
 *
 * Berbeda dari LinkStatus yang menggambarkan keadaan data tautan, enum ini
 * menggambarkan keputusan akhir yang diterima pengunjung.
 */
enum LinkAccessStatus: string
{
    case NotFound = 'not_found';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Scheduled = 'scheduled';
    case PasswordRequired = 'password_required';
    case WrongPassword = 'wrong_password';
    case TooManyAttempts = 'too_many_attempts';
    case Ready = 'ready';

    /**
     * Status klik yang perlu dicatat untuk hasil ini, bila ada.
     */
    public function clickStatus(): ?ClickStatus
    {
        return match ($this) {
            self::Inactive => ClickStatus::Inactive,
            self::Expired => ClickStatus::Expired,
            self::Scheduled => ClickStatus::Scheduled,
            self::PasswordRequired, self::TooManyAttempts => ClickStatus::Protected,
            self::WrongPassword => ClickStatus::WrongPassword,
            self::Ready => ClickStatus::Success,
            default => null,
        };
    }
}
