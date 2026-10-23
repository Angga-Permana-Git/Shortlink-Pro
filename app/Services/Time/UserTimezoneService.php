<?php

namespace App\Services\Time;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeZone;

/**
 * Menjembatani waktu tampilan pengguna dengan waktu server.
 *
 * Database tetap menyimpan waktu dalam zona aplikasi (APP_TIMEZONE), sehingga
 * data lama dan laporan admin tidak berubah. Hanya batas tampilan/input milik
 * pengguna yang dikonversi ke zona pilihannya.
 */
class UserTimezoneService
{
    public function zona(User $pengguna): string
    {
        $zona = (string) $pengguna->timezone;

        return in_array($zona, $this->semua(), true)
            ? $zona
            : (string) config('app.timezone', 'UTC');
    }

    public function label(string $zona): string
    {
        return match ($zona) {
            'Asia/Jakarta' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => str_replace('_', ' ', $zona),
        };
    }

    /** @return array<string, string> */
    public function daftar(): array
    {
        $daftar = [];

        foreach ($this->semua() as $zona) {
            $daftar[$zona] = $zona.' — '.$this->label($zona);
        }

        return $daftar;
    }

    /**
     * Mengubah waktu lokal pengguna menjadi waktu server untuk disimpan.
     */
    public function keServer(CarbonInterface|string|null $nilai, User $pengguna): ?string
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        $teks = $nilai instanceof CarbonInterface
            ? $nilai->format('Y-m-d H:i:s')
            : $nilai;

        return Carbon::parse($teks, $this->zona($pengguna))
            ->setTimezone((string) config('app.timezone', 'UTC'))
            ->format('Y-m-d H:i:s');
    }

    /**
     * Mengubah waktu server menjadi waktu lokal pengguna untuk ditampilkan.
     */
    public function kePengguna(?CarbonInterface $waktu, User $pengguna): ?CarbonInterface
    {
        return $waktu?->copy()->setTimezone($this->zona($pengguna));
    }

    public function format(?CarbonInterface $waktu, User $pengguna, string $format = 'd M Y H:i'): string
    {
        $lokal = $this->kePengguna($waktu, $pengguna);

        return $lokal
            ? $lokal->format($format).' '.$this->label($this->zona($pengguna))
            : '-';
    }

    /**
     * Siap dipakai kontrol `datetime-local` milik pengguna.
     */
    public function keLokal(CarbonInterface|string|null $nilai, User $pengguna, string $format = 'Y-m-d\TH:i'): ?string
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        $server = $nilai instanceof CarbonInterface
            ? $nilai
            : Carbon::parse($nilai, (string) config('app.timezone', 'UTC'));

        return $server->copy()->setTimezone($this->zona($pengguna))->format($format);
    }

    public function zonaSekarang(User $pengguna): CarbonInterface
    {
        return now()->setTimezone($this->zona($pengguna));
    }

    /** @return array<int, string> */
    private function semua(): array
    {
        return timezone_identifiers_list(DateTimeZone::ALL_WITH_BC);
    }
}
