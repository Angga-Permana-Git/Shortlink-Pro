<?php

namespace App\Http\Controllers;

use App\Models\BioProfile;

/**
 * Peta situs XML untuk mesin pencari.
 *
 * Hanya berisi halaman yang boleh diindeks: beranda, pendaftaran, masuk,
 * dan profil Bio publik yang aktif. Halaman redirect slug, QR, panel,
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
            ['url' => route('legal.tentang'), 'waktu' => now()],
            ['url' => route('legal.syarat'), 'waktu' => now()],
            ['url' => route('legal.privasi'), 'waktu' => now()],
        ]);

        $profil = BioProfile::query()
            ->where('is_active', true)
            ->whereNotNull('public_slug')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get(['public_slug', 'updated_at'])
            ->map(fn (BioProfile $item) => [
                'url' => route('bio.show', $item->public_slug),
                'waktu' => $item->updated_at,
            ]);

        $semua = $halaman->concat($profil);

        return response()
            ->view('sitemap', ['daftar' => $semua])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
