<?php

namespace App\Services\Dashboard;

use App\Models\LinktreeLink;
use App\Models\User;
use App\Services\Linktree\LinktreeAvatarService;
use App\Services\Linktree\LinktreeService;

/**
 * Data pratinjau halaman Linktree publik milik pengguna.
 *
 * `alamat` berisi alamat publik yang dipilih pemiliknya (`public_slug`), null
 * selama alamatnya belum diisi.
 */
class UserTinjauanLinktreeService
{
    public function __construct(
        private readonly LinktreeService $linktree,
        private readonly LinktreeAvatarService $avatar,
    ) {}

    /**
     * @return array{nama: string, bio: string|null, logo: string|null, terbit: bool, slug: string|null, alamat: string|null, tautan: array<int, array{label: string, url: string}>}
     */
    public function tinjauan(User $pengguna): array
    {
        $profil = $this->linktree->profile($pengguna);

        return [
            'nama' => $profil->display_name,
            'bio' => $profil->bio,
            'logo' => $this->avatar->url($profil),
            'terbit' => $profil->is_active,
            'slug' => $profil->public_slug,
            'alamat' => $profil->public_slug ? route('linktree.show', $profil->public_slug) : null,
            'tautan' => $profil->links
                ->map(fn (LinktreeLink $tautan): array => [
                    'label' => $tautan->label,
                    'url' => $tautan->url,
                ])
                ->all(),
        ];
    }
}
