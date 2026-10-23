<?php

namespace App\Dto;

/**
 * Data pembuatan tautan pendek, dipakai bersama oleh form publik, panel
 * pengguna, dan API.
 *
 * Bentuk data yang diterima ShortUrlService jadi tertulis di satu tempat,
 * sehingga pemanggil tidak lagi menebak nama kunci array.
 */
final readonly class CreateShortUrlDTO
{
    public function __construct(
        public string $destinationUrl,
        public ?string $customSlug = null,
        public bool $isActive = true,
        public ?string $startsAt = null,
        public ?string $expiresAt = null,
        public ?string $password = null,
    ) {}

    /**
     * Kunci `slug` pada array masukan diperlakukan sebagai slug kustom.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            destinationUrl: (string) $data['destination_url'],
            customSlug: self::textOrNull($data['slug'] ?? null),
            isActive: (bool) ($data['is_active'] ?? true),
            startsAt: self::textOrNull($data['starts_at'] ?? null),
            expiresAt: self::textOrNull($data['expires_at'] ?? null),
            password: self::textOrNull($data['password'] ?? null),
        );
    }

    private static function textOrNull(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
