<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Dasar semua pengecualian domain aplikasi.
 *
 * Setiap pengecualian mewarisi kelas ini dan menyediakan `errorCode()` yang
 * dipakai penangan galat untuk mengisi kode mesin pada respons
 * (disiplin_struktur.md bagian 5).
 */
class AppException extends RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly string $errorCode = 'APP_ERROR',
        private readonly int $status = 500,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function statusCode(): int
    {
        return $this->status;
    }
}
