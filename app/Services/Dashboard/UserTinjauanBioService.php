<?php

namespace App\Services\Dashboard;

use App\Models\BioLink;
use App\Models\User;
use App\Services\Bio\BioAvatarService;
use App\Services\Bio\BioService;

/**
 * Data pratinjau halaman Bio publik milik pengguna.
 *
 * `alamat` berisi alamat publik yang dipilih pemiliknya (`public_slug`), null
 * selama alamatnya belum diisi.
 */
class UserTinjauanBioService
{
    public function __construct(
        private readonly BioService $bio,
        private readonly BioAvatarService $avatar,
    ) {}

    /**
     * @return array{nama: string, bio: string|null, logo: string|null, terbit: bool, slug: string|null, alamat: string|null, tautan: array<int, array{label: string, url: string}>}
     */
    public function tinjauan(User $pengguna): array
    {
        $profil = $this->bio->profile($pengguna);

        return [
            'nama' => $profil->display_name,
            'bio' => $profil->bio,
            'logo' => $this->avatar->url($profil),
            'terbit' => $profil->is_active,
            'slug' => $profil->public_slug,
            'alamat' => $profil->public_slug ? route('bio.show', $profil->public_slug) : null,
            'tautan' => $profil->links
                ->map(fn (BioLink $tautan): array => [
                    'label' => $tautan->label,
                    'url' => $tautan->url,
                ])
                ->all(),
        ];
    }
}
