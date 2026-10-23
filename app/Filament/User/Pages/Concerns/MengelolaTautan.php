<?php

namespace App\Filament\User\Pages\Concerns;

use App\Dto\UpdateShortUrlDTO;
use App\Services\Dashboard\TautanSayaService;
use App\Services\ShortUrl\ShortUrlService;
use App\Services\Time\UserTimezoneService;
use App\Support\Validation\AturanTautanPendek;
use Illuminate\Support\Facades\Gate;

/**
 * Aksi kartu "Link Saya": salin, sunting, hapus, dan QR tiap tautan.
 *
 * kartu "Pemendek Tautan" ada di trait MembuatTautan, sedangkan pencarian,
 * filter, dan ekspor ada di MengelolaDaftarTautan. Penyimpanan tetap
 * didelegasikan ke ShortUrlService; yang diperiksa di sini hanya bentuk input,
 * kepemilikan tautan, dan izin aksinya.
 */
trait MengelolaTautan
{
    /**
     * Isian formulir sunting tautan; `protected` mengikuti UpdateShortUrlDTO.
     *
     * @var array<string, mixed>
     */
    public array $suntingan = [
        'destination_url' => '',
        'is_active' => true,
        'expires_at' => '',
        'password' => '',
        'protected' => false,
    ];

    public ?int $idSunting = null;

    /**
     * Tautan yang QR-nya sedang dibuka di kartu Link Saya, null bila tidak ada.
     */
    public ?int $idQr = null;

    public function mulaiSunting(int $idTautan): void
    {
        $this->suntingan = app(TautanSayaService::class)->suntingan(auth()->user(), $idTautan);
        $this->idSunting = $idTautan;

        $this->idQr = null;
        $this->hasilSlug = null;
        $this->hasilUrl = null;
        $this->pesanTautan = null;
    }

    public function batalSunting(): void
    {
        $this->idSunting = null;
        $this->reset('suntingan');
        $this->resetErrorBag('suntingan');
    }

    public function simpanSunting(int $idTautan): void
    {
        $tervalidasi = $this->validate(
            AturanTautanPendek::perubahan('suntingan.'),
            [],
            AturanTautanPendek::atribut('suntingan.'),
        )['suntingan'];

        $pengguna = auth()->user();
        $tautan = app(TautanSayaService::class)->milik($pengguna, $idTautan);

        Gate::authorize('update', $tautan);

        $tervalidasi['expires_at'] = app(UserTimezoneService::class)
            ->keServer($tervalidasi['expires_at'] ?? null, $pengguna);

        app(ShortUrlService::class)->update($tautan, $pengguna, UpdateShortUrlDTO::fromArray($tervalidasi));

        $this->batalSunting();
        $this->pesanTautan = 'Tautan diperbarui.';
    }

    /**
     * Buka kartu QR milik satu tautan. Tautan milik pengguna lain diperlakukan
     * sebagai tidak ada, sama seperti aksi sunting dan hapus.
     */
    public function tampilkanQr(int $idTautan): void
    {
        app(TautanSayaService::class)->milik(auth()->user(), $idTautan);

        $this->idSunting = null;
        $this->idQr = $idTautan;
        $this->pesanTautan = null;
    }

    public function sembunyikanQr(): void
    {
        $this->idQr = null;
    }

    public function hapusTautan(int $idTautan): void
    {
        $pengguna = auth()->user();
        $tautan = app(TautanSayaService::class)->milik($pengguna, $idTautan);

        Gate::authorize('delete', $tautan);

        app(ShortUrlService::class)->delete($tautan, $pengguna);

        if ($this->idSunting === $idTautan) {
            $this->batalSunting();
        }

        if ($this->idQr === $idTautan) {
            $this->idQr = null;
        }

        // Tautan yang hilang tidak boleh tertinggal sebagai pilihan Linktree.
        $this->pilihanLinktree = array_values(array_diff($this->pilihanLinktree, [$idTautan]));
        $this->pesanTautan = 'Tautan dihapus.';
    }
}
