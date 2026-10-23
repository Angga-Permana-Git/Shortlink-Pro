<?php

namespace App\Services\Auth;

use App\Models\User;

/**
 * Menentukan halaman tujuan setelah pengguna berhasil masuk.
 *
 * Dipakai bersama oleh LoginController, AuthenticateLocalController, dan
 * HandleKeycloakCallbackController supaya aturannya hanya ada di satu tempat.
 */
class LandingService
{
    public function for(?User $user): string
    {
        return $user?->isAdmin() ? '/admin' : '/user';
    }
}
