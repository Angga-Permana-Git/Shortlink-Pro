<?php

namespace App\Dto;

/**
 * Data perubahan branding aplikasi.
 *
 * Properti yang bernilai null tidak diubah; hanya properti yang diisi
 * pengguna yang ditulis ke pengaturan.
 */
final readonly class UpdateBrandingDTO
{
    public function __construct(
        public ?string $appName = null,
        public ?string $brandColor = null,
        public ?string $loginLogo = null,
        public ?string $favicon = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            appName: self::textOrNull($data['app_name'] ?? null),
            brandColor: self::textOrNull($data['brand_color'] ?? null),
            loginLogo: self::textOrNull($data['login_logo'] ?? null),
            favicon: self::textOrNull($data['favicon'] ?? null),
        );
    }

    private static function textOrNull(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
