<?php

namespace App\Filament\User\Pages\Concerns;

use App\Services\Time\UserTimezoneService;
use DateTimeZone;
use Illuminate\Validation\Rule;

/**
 * Preferensi zona waktu pengguna. Waktu tetap disimpan dalam zona server;
 * nilai ini hanya dipakai saat menampilkan dan menerima waktu dari pengguna.
 */
trait MengelolaZonaWaktu
{
    public string $zonaWaktu = '';

    public ?string $pesanZonaWaktu = null;

    public function mountZonaWaktu(): void
    {
        $this->zonaWaktu = app(UserTimezoneService::class)->zona(auth()->user());
    }

    public function simpanZonaWaktu(): void
    {
        $this->validate([
            'zonaWaktu' => [
                'required',
                Rule::in(timezone_identifiers_list(DateTimeZone::ALL_WITH_BC)),
            ],
        ], [], ['zonaWaktu' => __('ui.panel.zona_waktu')]);

        $pengguna = auth()->user();

        if ($pengguna->timezone !== $this->zonaWaktu) {
            $pengguna->timezone = $this->zonaWaktu;
            $pengguna->save();
        }

        $this->pesanZonaWaktu = __('ui.panel.zona_waktu_tersimpan');
    }

    /**
     * Dipanggil sekali dari browser saat pengguna belum punya preferensi.
     */
    public function deteksiZonaWaktu(string $zona): void
    {
        if (filled(auth()->user()->timezone)) {
            return;
        }

        if (! in_array($zona, timezone_identifiers_list(DateTimeZone::ALL_WITH_BC), true)) {
            return;
        }

        $this->zonaWaktu = $zona;
        $this->simpanZonaWaktu();
    }
}
