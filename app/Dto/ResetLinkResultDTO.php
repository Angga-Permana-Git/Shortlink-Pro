<?php

namespace App\Dto;

use App\Support\Enums\ResetLinkOutcome;

/**
 * Hasil permintaan tautan atur ulang kata sandi.
 *
 * previewUrl berisi tautan atur ulang yang boleh ditampilkan di layar; hanya
 * diisi saat email tidak benar-benar terkirim (mailer `log` pada pengembangan),
 * sehingga di produksi nilainya selalu null. provider diisi hanya ketika akun
 * bukan akun lokal (nilainya `google` atau `keycloak`).
 */
final readonly class ResetLinkResultDTO
{
    public function __construct(
        public ResetLinkOutcome $outcome,
        public ?string $previewUrl = null,
        public ?string $provider = null,
    ) {}
}
