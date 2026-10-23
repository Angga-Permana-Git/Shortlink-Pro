<?php

namespace App\Support\Enums;

/**
 * Hasil pengaturan kata sandi baru lewat tautan atur ulang.
 *
 * Controller hanya menerjemahkan enum ini menjadi pesan dan respons.
 */
enum PasswordUpdateOutcome: string
{
    case Updated = 'updated';
    case InvalidToken = 'invalid_token';
    case ExternalAccount = 'external_account';
    case Throttled = 'throttled';
}
