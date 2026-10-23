<?php

namespace App\Services\Auth;

use App\Dto\ResetLinkResultDTO;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\Audit\AuditLogger;
use App\Support\Enums\PasswordUpdateOutcome;
use App\Support\Enums\ResetLinkOutcome;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Atur ulang kata sandi untuk akun lokal saja.
 *
 * Akun yang dibuat lewat penyedia identitas (Google atau SSO kantor) tidak
 * punya kata sandi, jadi permintaannya dijawab dengan penjelasan penyedianya,
 * bukan dikirimi tautan. Token, masa berlaku, dan pembatasan percobaan tetap
 * memakai broker bawaan Laravel lewat facade Password.
 */
class PasswordResetService
{
    public function mintaTautan(string $email): ResetLinkResultDTO
    {
        $user = $this->akunLokal($email);

        if (! $user) {
            return new ResetLinkResultDTO(ResetLinkOutcome::UnknownEmail);
        }

        if (! $user->isLocal()) {
            return new ResetLinkResultDTO(ResetLinkOutcome::ExternalAccount, provider: $user->auth_provider);
        }

        if (! $user->canLogin()) {
            return new ResetLinkResultDTO(ResetLinkOutcome::UnknownEmail);
        }

        $tautan = null;

        $status = Password::sendResetLink(
            ['email' => $user->email],
            function (User $pengguna, string $token) use (&$tautan): void {
                $pengguna->notify(new ResetPasswordNotification($token));
                $tautan = $this->tautanReset($pengguna, $token);
            },
        );

        return match ($status) {
            Password::RESET_LINK_SENT => new ResetLinkResultDTO(
                ResetLinkOutcome::Sent,
                $this->tampilkanTautan() ? $tautan : null,
            ),
            Password::RESET_THROTTLED => new ResetLinkResultDTO(ResetLinkOutcome::Throttled),
            default => new ResetLinkResultDTO(ResetLinkOutcome::UnknownEmail),
        };
    }

    /**
     * @param  array<string, string>  $kredensial
     */
    public function aturUlang(array $kredensial): PasswordUpdateOutcome
    {
        $user = $this->akunLokal((string) ($kredensial['email'] ?? ''));

        if ($user && ! $user->isLocal()) {
            return PasswordUpdateOutcome::ExternalAccount;
        }

        $status = Password::reset($kredensial, function (User $pengguna, string $kataSandi): void {
            $pengguna->forceFill(['password' => $kataSandi]);
            $pengguna->setRememberToken(Str::random(60));
            $pengguna->save();

            AuditLogger::log($pengguna, 'auth.password_reset', 'user', $pengguna->id);
        });

        return match ($status) {
            Password::PASSWORD_RESET => PasswordUpdateOutcome::Updated,
            Password::RESET_THROTTLED => PasswordUpdateOutcome::Throttled,
            default => PasswordUpdateOutcome::InvalidToken,
        };
    }

    private function akunLokal(string $email): ?User
    {
        $email = strtolower(trim($email));

        return $email === '' ? null : User::query()->whereRaw('lower(email) = ?', [$email])->first();
    }

    private function tautanReset(User $user, string $token): string
    {
        return route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]);
    }

    /**
     * Tautan ditampilkan di layar hanya saat email tidak benar-benar terkirim,
     * yaitu ketika mailer disetel `log` (pengembangan), supaya alurnya bisa diuji.
     */
    private function tampilkanTautan(): bool
    {
        return config('mail.default') === 'log';
    }
}
