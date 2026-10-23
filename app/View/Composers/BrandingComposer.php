<?php

namespace App\View\Composers;

use App\Services\Branding\BrandingService;
use Illuminate\View\View;

/**
 * Menyiapkan data branding untuk tampilan publik, halaman masuk, dan halaman
 * profil Linktree.
 *
 * Tampilan hanya menerima data siap pakai; tidak ada tampilan yang membaca
 * database sendiri (disiplin_struktur.md bagian 1).
 */
class BrandingComposer
{
    public function __construct(private readonly BrandingService $branding) {}

    public function compose(View $view): void
    {
        $view->with('branding', [
            'nama' => $this->branding->appName(),
            'logo' => $this->branding->loginLogoUrl(),
            'warna' => $this->branding->brandColor(),
        ]);
    }
}
