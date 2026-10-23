<?php

namespace App\Services\Linktree;

use App\Dto\LinktreeLinkDTO;
use App\Dto\UpdateLinktreeProfileDTO;
use App\Models\LinktreeProfile;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class LinktreeService
{
    /**
     * Ambil profil Linktree milik pengguna, buat bila belum ada.
     */
    public function profile(User $user): LinktreeProfile
    {
        return LinktreeProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->name, 'bio' => null, 'is_active' => true],
        );
    }

    /**
     * Profil yang boleh dibuka pengunjung: profil aktif dengan alamat publik
     * (public_slug) tersebut, beserta tautan aktifnya saja.
     *
     * @throws ModelNotFoundException
     */
    public function publicProfile(string $slug): LinktreeProfile
    {
        return LinktreeProfile::query()
            ->with(['links' => fn ($query) => $query->where('is_active', true)])
            ->where('public_slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Perbarui profil. Hanya kolom yang diizinkan yang dipakai, sehingga
     * pemanggil tidak bisa memindahkan profil ke pengguna lain.
     */
    public function update(User $user, UpdateLinktreeProfileDTO $data): LinktreeProfile
    {
        $profile = $this->profile($user);

        $profile->fill([
            'public_slug' => $data->publicSlug ?? $profile->public_slug,
            'display_name' => $data->displayName ?? $profile->display_name,
            'bio' => $data->bioIsSet ? $data->bio : $profile->bio,
            'is_active' => $data->isActive ?? $profile->is_active,
        ])->save();

        AuditLogger::log($user, 'linktree.update', 'linktree_profile', $profile->id);

        return $profile->refresh();
    }

    /**
     * Ganti seluruh daftar tautan dengan urutan sesuai masukan pengguna.
     *
     * Repeater Filament tidak mengirim id, jadi daftar ditulis ulang dalam satu
     * transaksi: lebih sederhana daripada mencocokkan baris lama satu per satu.
     *
     * @param  array<int, LinktreeLinkDTO>  $links
     */
    public function syncLinks(LinktreeProfile $profile, array $links): void
    {
        DB::transaction(function () use ($profile, $links): void {
            $profile->links()->delete();

            foreach (array_values($links) as $index => $link) {
                $profile->links()->create([
                    'label' => $link->label,
                    'url' => $link->url,
                    'sort_order' => $index,
                    'is_active' => $link->isActive,
                ]);
            }
        });

        AuditLogger::log($profile->user, 'linktree.links_sync', 'linktree_profile', $profile->id, [
            'total' => count($links),
        ]);
    }
}
