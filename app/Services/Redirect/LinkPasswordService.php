<?php

namespace App\Services\Redirect;

use App\Models\ShortUrl;
use App\Support\Enums\LinkAccessStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Pemeriksaan kata sandi tautan beserta batas percobaannya.
 *
 * Dipisah dari LinkAccessService supaya aturan pembatasan percobaan punya satu
 * tempat sendiri (disiplin_struktur.md bagian 2).
 */
class LinkPasswordService
{
    public function verify(ShortUrl $shortUrl, string $password): LinkAccessStatus
    {
        $key = $this->attemptKey($shortUrl);
        $maxAttempts = (int) config('pemendek.password_max_attempts', 5);
        $lockSeconds = (int) config('pemendek.password_lock_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return LinkAccessStatus::TooManyAttempts;
        }

        if (! Hash::check($password, $shortUrl->password_hash)) {
            RateLimiter::hit($key, $lockSeconds);

            return LinkAccessStatus::WrongPassword;
        }

        RateLimiter::clear($key);

        return LinkAccessStatus::Ready;
    }

    private function attemptKey(ShortUrl $shortUrl): string
    {
        return 'shortlink-password:'.$shortUrl->id;
    }
}
