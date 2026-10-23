<?php

namespace App\Services\Auth;

use App\Dto\RequestContextDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\Auth\UsernameAkun;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Penyiapan akun dari klaim Keycloak.
 *
 * Dipisah dari KeycloakAuthService supaya urusan "menerjemahkan klaim menjadi
 * akun" tidak bercampur dengan urusan memasang sesi.
 */
class KeycloakAccountService
{
    public function __construct(private readonly LoginAuditRecorder $audit) {}

    /**
     * Ambil username dari klaim, tolak yang tidak layak, lalu simpan atau
     * perbarui akun berdasarkan subjek Keycloak.
     *
     * @throws AuthorizationException
     */
    public function provision(SocialiteUser $socialUser, RequestContextDTO $context): User
    {
        $subject = (string) $socialUser->getId();
        $username = strtolower(trim((string) ($socialUser->getNickname() ?? '')));
        $name = $socialUser->getName() ?? $username;
        $email = $socialUser->getEmail();

        $this->rejectReservedUsername($username, $subject, $context);

        $user = User::query()->where('keycloak_sub', $subject)->first();

        if (! $user) {
            return User::create([
                'keycloak_sub' => $subject,
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'auth_provider' => 'keycloak',
                'role' => 'user',
                'is_active' => true,
                'is_protected' => false,
                'last_login_at' => now(),
            ]);
        }

        if (! $user->isActive()) {
            $this->audit->record($user->getKey(), $username, 'keycloak', 'failed', $context);

            throw new AuthorizationException('Akun Anda dinonaktifkan.');
        }

        $user->forceFill([
            'username' => $username,
            'name' => $name,
            'email' => $email,
            'auth_provider' => 'keycloak',
            'last_login_at' => now(),
        ])->save();

        return $user;
    }

    private function rejectReservedUsername(string $username, string $subject, RequestContextDTO $context): void
    {
        if ($username !== '' && ! in_array($username, UsernameAkun::CADANGAN, true)) {
            return;
        }

        $this->audit->record(null, $subject, 'keycloak', 'failed', $context);
        AuditLogger::log(null, 'auth.login_failed', 'user', null, [
            'provider' => 'keycloak',
            'reason' => 'Reserved or empty username',
        ]);

        throw new AuthorizationException('Login SSO ditolak karena username tidak valid.');
    }
}
