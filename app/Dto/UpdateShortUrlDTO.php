<?php

namespace App\Dto;

/**
 * Data perubahan tautan pendek.
 *
 * Dua nilai null di sini punya arti khusus:
 * - isActive null berarti keaktifan tidak diubah.
 * - protected null atau true berarti kata sandi lama dipertahankan bila
 *   password kosong; protected false berarti kata sandi dilepas.
 */
final readonly class UpdateShortUrlDTO
{
    public function __construct(
        public string $destinationUrl,
        public ?bool $isActive = null,
        public ?string $startsAt = null,
        public ?string $expiresAt = null,
        public ?string $password = null,
        public ?bool $protected = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            destinationUrl: (string) $data['destination_url'],
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            startsAt: self::textOrNull($data['starts_at'] ?? null),
            expiresAt: self::textOrNull($data['expires_at'] ?? null),
            password: self::textOrNull($data['password'] ?? null),
            protected: array_key_exists('protected', $data) ? (bool) $data['protected'] : null,
        );
    }

    private static function textOrNull(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
