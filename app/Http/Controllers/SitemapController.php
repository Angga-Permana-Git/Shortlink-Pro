<?php

namespace App\Http\Controllers;

use App\Models\LinktreeProfile;

/**
 * Peta situs XML untuk mesin pencari.
 *
 * Hanya berisi halaman yang boleh diindeks: beranda, pendaftaran, masuk,
 * dan profil Linktree publik yang aktif. Halaman redirect slug, QR, panel,
 * dan halaman reset kata sandi sengaja tidak dimasukkan agar jatah crawl
 * tidak terbuang.
 */
class SitemapController extends Controller
{
    public function __invoke(): \Illuminate\Http\Response
    {
        $halaman = collect([
            ['url' => url('/'), 'waktu' => now()],
            ['url' => route('register'), 'waktu' => now()],
            ['url' => route('login'), 'waktu' => now()],
            ['url' => route('login.local'), 'waktu' => now()],
        ]);

        $profil = LinktreeProfile::query()
            ->where('is_active', true)
            ->whereNotNull('public_slug')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get(['public_slug', 'updated_at'])
            ->map(fn (LinktreeProfile $item) => [
                'url' => route('linktree.show', $item->public_slug),
                'waktu' => $item->updated_at,
            ]);

        $semua = $halaman->concat($profil);

        return response()
            ->view('sitemap', ['daftar' => $semua])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
