<?php

namespace App\Support\Enums;

/**
 * Hasil percobaan masuk dengan akun lokal.
 *
 * Controller hanya perlu menerjemahkan enum ini menjadi pesan dan respons,
 * sedangkan pencatatan jejaknya sudah dilakukan LocalAuthService.
 */
enum LoginOutcome: string
{
    case Success = 'success';
    case InvalidCredentials = 'invalid_credentials';
    case TooManyAttempts = 'too_many_attempts';
}
