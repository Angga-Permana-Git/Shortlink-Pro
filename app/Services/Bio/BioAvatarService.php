<?php

namespace App\Services\Bio;

use App\Models\BioProfile;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\Keamanan\BerkasLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Logo profil Bio milik pengguna.
 *
 * Berkas disimpan di disk `public` — sama seperti logo branding — supaya bisa
 * langsung ditampilkan halaman profil publik `/u/{username}`. Nama berkas
 * dibangkitkan sendiri dan ekstensinya ditentukan dari isi berkas, sehingga
 * berkas yang bisa dieksekusi tidak pernah tersimpan.
 */
class BioAvatarService
{
    private const DISK = 'public';

    private const FOLDER = 'bio';

    public function __construct(
        private readonly BioService $bio,
        private readonly BerkasLogo $berkasLogo,
    ) {}

    /**
     * Alamat gambar logo, atau null bila profil belum punya logo.
     */
    public function url(BioProfile $profil): ?string
    {
        if (! $profil->avatar_path) {
            return null;
        }

        return Storage::disk(self::DISK)->url($profil->avatar_path);
    }

    public function simpan(User $pengguna, UploadedFile $berkas): BioProfile
    {
        $ekstensi = $this->berkasLogo->ekstensi($berkas);

        if ($ekstensi === null) {
            throw ValidationException::withMessages(['profil.logo' => __('ui.logo.tipe')]);
        }

        if ($ekstensi === 'svg' && ! $this->berkasLogo->svgAman($berkas)) {
            throw ValidationException::withMessages(['profil.logo' => __('ui.logo.svg_berbahaya')]);
        }

        $profil = $this->bio->profile($pengguna);

        $this->hapusBerkas($profil);

        $nama = $pengguna->getKey().'-'.Str::random(32).'.'.$ekstensi;

        $profil->avatar_path = $berkas->storeAs(self::FOLDER.'/'.$pengguna->getKey(), $nama, self::DISK);
        $profil->save();

        AuditLogger::log($pengguna, 'bio.avatar_upload', 'bio_profile', $profil->id);

        return $profil->refresh();
    }

    public function hapus(User $pengguna): void
    {
        $profil = $this->bio->profile($pengguna);

        $this->hapusBerkas($profil);

        $profil->avatar_path = null;
        $profil->save();

        AuditLogger::log($pengguna, 'bio.avatar_delete', 'bio_profile', $profil->id);
    }

    /**
     * Buang berkas lama supaya tidak ada gambar yatim di disk publik.
     */
    private function hapusBerkas(BioProfile $profil): void
    {
        if ($profil->avatar_path && Storage::disk(self::DISK)->exists($profil->avatar_path)) {
            Storage::disk(self::DISK)->delete($profil->avatar_path);
        }
    }
}
