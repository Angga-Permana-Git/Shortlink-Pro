<?php

namespace App\Dto;

/**
 * Satu baris tautan pada halaman Linktree.
 *
 * Urutan daftar ditentukan oleh posisi di dalam array saat disimpan, bukan
 * oleh properti di DTO ini.
 */
final readonly class LinktreeLinkDTO
{
    public function __construct(
        public string $label,
        public string $url,
        public bool $isActive = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            label: (string) $data['label'],
            url: (string) $data['url'],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
