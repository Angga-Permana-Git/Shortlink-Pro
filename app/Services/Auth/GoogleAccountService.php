<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\Auth\UsernameAkun;
use App\Support\Enums\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Penyiapan akun dari klaim Google.
 *
 * Pencocokan akun: google_sub dahulu, lalu email terverifikasi. Akun lokal
 * yang emailnya sama ditautkan (kata sandinya tetap bisa dipakai), sedangkan
 * email milik penyedia lain, email yang belum terverifikasi, dan akun nonaktif
 * ditolak sebagai AuthorizationException supaya service ini tidak tahu soal
 * status HTTP (disiplin_struktur.md bagian 1).
 */
class GoogleAccountService
{
    public function __construct(private readonly LoginAuditRecorder $audit) {}

    /**
     * @throws AuthorizationException
     */
    public function provision(SocialiteUser $socialUser, RequestContextDTO $context): User
    {
        $subject = (string) $socialUser->getId();
        $email = strtolower(trim((string) $socialUser->getEmail()));

        if ($email === '' || ! $this->emailTerverifikasi($socialUser)) {
            $this->tolak(null, $email !== '' ? $email : $subject, 'email Google tidak terverifikasi', $context);
        }

        $user = $this->cocokkan($socialUser, $subject, $email, $context);

        if (! $user->isActive()) {
            $this->tolak($user->getKey(), $email, 'akun dinonaktifkan', $context);
        }

        $user->forceFill([
            'google_sub' => $subject,
            'name' => $socialUser->getName() ?: $user->name,
            'email' => $email,
            'last_login_at' => now(),
        ])->save();

        return $user;
    }

    /**
     * Ambil akun yang cocok, atau buat akun baru bila emailnya belum terpakai.
     */
    private function cocokkan(SocialiteUser $socialUser, string $subject, string $email, RequestContextDTO $context): User
    {
        $olehSubjek = User::query()->where('google_sub', $subject)->first();

        if ($olehSubjek) {
            return $olehSubjek;
        }

        $olehEmail = User::query()->where('email', $email)->first();

        if ($olehEmail && $olehEmail->isLocal()) {
            return $olehEmail;
        }

        if ($olehEmail) {
            $this->tolak($olehEmail->getKey(), $email, 'email sudah dipakai penyedia masuk lain', $context);
        }

        return User::create([
            'google_sub' => $subject,
            'username' => UsernameAkun::dariEmail($email),
            'name' => $socialUser->getName() ?: Str::headline(Str::before($email, '@')),
            'email' => $email,
            'auth_provider' => 'google',
            'role' => Role::User->value,
            'is_active' => true,
            'is_protected' => false,
        ]);
    }

    /**
     * Google menandai email yang sudah diverifikasi pada klaimnya. Klaim yang
     * tidak ada atau bukan true dianggap belum terverifikasi, supaya penautan ke
     * akun lokal tidak pernah terjadi atas dasar email yang belum terbukti.
     */
    private function emailTerverifikasi(SocialiteUser $socialUser): bool
    {
        return (bool) filter_var($socialUser->getRaw()['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws AuthorizationException
     */
    private function tolak(?int $userId, string $username, string $alasan, RequestContextDTO $context): never
    {
        $this->audit->record($userId, $username, 'google', 'failed', $context);
        AuditLogger::log($userId, 'auth.login_failed', 'user', $userId, [
            'provider' => 'google',
            'reason' => $alasan,
        ]);

        throw new AuthorizationException('Masuk dengan Google ditolak: '.$alasan.'.');
    }
}
