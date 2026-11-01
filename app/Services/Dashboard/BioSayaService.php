<?php

namespace App\Services\Dashboard;

use App\Dto\BioLinkDTO;
use App\Dto\UpdateBioProfileDTO;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Bio\BioService;

/**
 * Perubahan halaman Bio milik pengguna dari halaman panel: pilihan tautan
 * yang tampil, status terbit, dan identitas profilnya.
 */
class BioSayaService
{
    public function __construct(private readonly BioService $bio) {}

    /**
     * Simpan pilihan tautan yang tampil di halaman Bio sekaligus status
     * terbitnya.
     *
     * @param  array<int, int>  $idTautan
     */
    public function simpanPilihan(User $pengguna, array $idTautan, bool $terbit): void
    {
        $profil = $this->bio->profile($pengguna);

        $sebelumnya = $profil->links()->get()->keyBy('url');

        $tautan = ShortUrl::query()
            ->where('owner_id', $pengguna->id)
            ->whereIn('id', $idTautan)
            ->latest()
            ->get();

        $this->bio->syncLinks($profil, $tautan
            ->map(function (ShortUrl $item) use ($sebelumnya): BioLinkDTO {
                $lama = $sebelumnya->get($item->shortUrl());

                return new BioLinkDTO(
                    label: $lama?->label ?? $item->slug,
                    url: $item->shortUrl(),
                    isActive: $lama?->is_active ?? true,
                );
            })
            ->all());

        $this->bio->update($pengguna, new UpdateBioProfileDTO(isActive: $terbit));
    }

    /**
     * Simpan nama tampilan, keterangan, dan alamat publik profil.
     *
     * bio selalu ikut dikirim (`bioIsSet`) supaya pengguna bisa mengosongkannya.
     */
    public function simpanProfil(User $pengguna, string $nama, ?string $bio, string $alamat): void
    {
        $this->bio->update($pengguna, new UpdateBioProfileDTO(
            displayName: $nama,
            bio: $bio,
            bioIsSet: true,
            publicSlug: $alamat,
        ));
    }
}
