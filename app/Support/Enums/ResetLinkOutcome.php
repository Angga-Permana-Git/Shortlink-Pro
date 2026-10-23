<?php

namespace App\Support\Enums;

/**
 * Hasil permintaan tautan atur ulang kata sandi.
 *
 * Controller hanya menerjemahkan enum ini menjadi pesan dan respons.
 */
enum ResetLinkOutcome: string
{
    case Sent = 'sent';
    case UnknownEmail = 'unknown_email';
    case ExternalAccount = 'external_account';
    case Throttled = 'throttled';
}
