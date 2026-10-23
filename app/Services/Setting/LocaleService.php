<?php

namespace App\Services\Setting;

use App\Models\User;
use App\Services\Audit\AuditLogger;

/**
 * Penentu bahasa antarmuka per pengguna.
 *
 * Tamu memakai bahasa yang tersimpan di sesinya sendiri; pengguna masuk memakai
 * kolom `users.locale` miliknya. Dengan begitu pilihan satu orang tidak pernah
 * mengubah pilihan orang lain.
 */
class LocaleService
{
    /**
     * @return array<string, string>
     */
    public function tersedia(): array
    {
        return (array) config('bahasa.tersedia', ['id' => 'Bahasa Indonesia']);
    }

    /**
     * Kode bahasa bila termasuk daftar yang didukung, selain itu null.
     */
    public function sah(?string $locale): ?string
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        return array_key_exists($locale, $this->tersedia()) ? $locale : null;
    }

    /**
     * Urutan penentuan: pilihan pengguna, pilihan sesi tamu, lalu bahasa bawaan.
     */
    public function aktif(?User $pengguna = null, ?string $sesi = null): string
    {
        return $this->sah($pengguna?->locale)
            ?? $this->sah($sesi)
            ?? (string) config('bahasa.bawaan', 'id');
    }

    /**
     * Simpan pilihan bahasa milik satu pengguna beserta jejak auditnya.
     */
    public function simpan(User $pengguna, string $locale): void
    {
        $locale = (string) $this->sah($locale);

        if ($locale === '' || $pengguna->locale === $locale) {
            return;
        }

        $pengguna->forceFill(['locale' => $locale])->save();

        AuditLogger::log($pengguna, 'locale.update', 'user', $pengguna->getKey(), [
            'locale' => $locale,
        ]);
    }
}
