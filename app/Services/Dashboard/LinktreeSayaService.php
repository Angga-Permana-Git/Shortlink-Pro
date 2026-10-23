<?php

namespace App\Services\Dashboard;

use App\Dto\LinktreeLinkDTO;
use App\Dto\UpdateLinktreeProfileDTO;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Linktree\LinktreeService;

/**
 * Perubahan halaman Linktree milik pengguna dari halaman panel: pilihan tautan
 * yang tampil, status terbit, dan identitas profilnya.
 */
class LinktreeSayaService
{
    public function __construct(private readonly LinktreeService $linktree) {}

    /**
     * Simpan pilihan tautan yang tampil di halaman Linktree sekaligus status
     * terbitnya.
     *
     * @param  array<int, int>  $idTautan
     */
    public function simpanPilihan(User $pengguna, array $idTautan, bool $terbit): void
    {
        $profil = $this->linktree->profile($pengguna);

        $sebelumnya = $profil->links()->get()->keyBy('url');

        $tautan = ShortUrl::query()
            ->where('owner_id', $pengguna->id)
            ->whereIn('id', $idTautan)
            ->latest()
            ->get();

        $this->linktree->syncLinks($profil, $tautan
            ->map(function (ShortUrl $item) use ($sebelumnya): LinktreeLinkDTO {
                $lama = $sebelumnya->get($item->shortUrl());

                return new LinktreeLinkDTO(
                    label: $lama?->label ?? $item->slug,
                    url: $item->shortUrl(),
                    isActive: $lama?->is_active ?? true,
                );
            })
            ->all());

        $this->linktree->update($pengguna, new UpdateLinktreeProfileDTO(isActive: $terbit));
    }

    /**
     * Simpan nama tampilan, keterangan, dan alamat publik profil.
     *
     * bio selalu ikut dikirim (`bioIsSet`) supaya pengguna bisa mengosongkannya.
     */
    public function simpanProfil(User $pengguna, string $nama, ?string $bio, string $alamat): void
    {
        $this->linktree->update($pengguna, new UpdateLinktreeProfileDTO(
            displayName: $nama,
            bio: $bio,
            bioIsSet: true,
            publicSlug: $alamat,
        ));
    }
}
