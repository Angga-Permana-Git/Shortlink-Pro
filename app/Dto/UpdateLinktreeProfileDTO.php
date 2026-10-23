<?php

namespace App\Dto;

/**
 * Data perubahan profil Linktree.
 *
 * Nilai null berarti kolom tidak diubah, sehingga pemanggil tidak bisa
 * memindahkan profil ke pengguna lain (tidak ada properti user_id di sini).
 * bioIsSet menandai bahwa pemanggil memang mengirim kolom bio, sehingga bio
 * boleh dikosongkan. publicSlug adalah alamat publik halaman (`/u/{publicSlug}`)
 * yang dipilih pemiliknya sendiri, bukan username login.
 */
final readonly class UpdateLinktreeProfileDTO
{
    public function __construct(
        public ?string $displayName = null,
        public ?string $bio = null,
        public bool $bioIsSet = false,
        public ?bool $isActive = null,
        public ?string $publicSlug = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $bioIsSet = array_key_exists('bio', $data);
        $bio = $data['bio'] ?? null;

        return new self(
            displayName: isset($data['display_name']) ? (string) $data['display_name'] : null,
            bio: $bio === null || $bio === '' ? null : (string) $bio,
            bioIsSet: $bioIsSet,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            publicSlug: ($data['public_slug'] ?? '') === '' ? null : (string) $data['public_slug'],
        );
    }
}
