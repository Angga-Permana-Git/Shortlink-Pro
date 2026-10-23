<?php

namespace App\Services\Dashboard;

use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Time\UserTimezoneService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Penelusuran tautan milik pengguna dari halaman panel.
 *
 * Setiap pencarian dibatasi kolom `owner_id`, sehingga id yang dikirim dari
 * halaman tidak bisa dipakai untuk menyentuh tautan milik orang lain
 * (PRDapp.md bagian 3.4). Izin hapus dan ubah sendiri tetap diperiksa
 * ShortUrlPolicy di kelas halaman.
 */
class TautanSayaService
{
    public function __construct(
        private readonly UserTimezoneService $waktu,
    ) {}

    /**
     * @throws ModelNotFoundException
     */
    public function milik(User $pengguna, int $idTautan): ShortUrl
    {
        return ShortUrl::query()
            ->where('owner_id', $pengguna->id)
            ->whereKey($idTautan)
            ->firstOrFail();
    }

    /**
     * Isian awal formulir sunting untuk satu tautan milik pengguna.
     *
     * Waktu kedaluwarsa dikirim dalam bentuk yang diminta kontrol
     * `datetime-local`, mengikuti kebiasaan service dashboard lain yang
     * mengembalikan nilai siap tampil (bandingkan `tinjauanLinktree`).
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function suntingan(User $pengguna, int $idTautan): array
    {
        $tautan = $this->milik($pengguna, $idTautan);

        return [
            'destination_url' => $tautan->destination_url,
            'is_active' => $tautan->is_active,
            'expires_at' => $this->waktu->keLokal($tautan->expires_at, $pengguna),
            'password' => '',
            'protected' => $tautan->hasPassword(),
        ];
    }
}
