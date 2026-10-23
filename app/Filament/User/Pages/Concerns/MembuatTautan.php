<?php

namespace App\Filament\User\Pages\Concerns;

use App\Dto\CreateShortUrlDTO;
use App\Services\ShortUrl\ShortUrlService;
use App\Services\Time\UserTimezoneService;
use App\Support\Validation\AturanTautanPendek;

/**
 * Aksi kartu "Pemendek Tautan".
 *
 * Dipisah dari MengelolaTautan (kartu Link Saya) agar tiap berkas tetap di bawah
 * batas ukuran (disiplin_struktur.md bagian 2). Penyimpanan didelegasikan ke
 * ShortUrlService; yang diperiksa di sini hanya bentuk input.
 */
trait MembuatTautan
{
    /**
     * Isian formulir pemendek tautan. Kunci mengikuti nama kolom kanonik agar
     * aturan validasi bersama bisa dipakai apa adanya.
     *
     * @var array<string, string>
     */
    public array $data = [
        'destination_url' => '',
        'slug' => '',
        'expires_at' => '',
        'password' => '',
    ];

    /**
     * Tautan pendek yang baru dibuat, dipakai kartu hasil beserta QR-nya.
     */
    public ?string $hasilUrl = null;

    public ?string $hasilSlug = null;

    public function buatTautan(): void
    {
        $tervalidasi = $this->validate(
            AturanTautanPendek::semua('data.'),
            [],
            AturanTautanPendek::atribut('data.'),
        );

        $pengguna = auth()->user();

        $tervalidasi['data']['expires_at'] = app(UserTimezoneService::class)
            ->keServer($tervalidasi['data']['expires_at'] ?? null, $pengguna);

        $tautan = app(ShortUrlService::class)->create(
            $pengguna,
            CreateShortUrlDTO::fromArray($tervalidasi['data']),
        );

        $this->hasilSlug = $tautan->slug;
        $this->hasilUrl = $tautan->shortUrl();
        $this->pesanTautan = null;

        $this->reset('data');

        $this->dispatch('tautan-dibuat');
    }
}
