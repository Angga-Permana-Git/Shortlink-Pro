<?php

namespace App\Http\Controllers;

use App\Services\Setting\OperationalSettingsService;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Halaman informasi publik: Tentang, Syarat & Ketentuan, Kebijakan Privasi.
 *
 * Satu controller untuk tiga halaman statis agar teks legal (DFB Bagian 2-3)
 * tidak tersebar di banyak file. Slug dilindungi ganda: route didaftar
 * statis SEBELUM catch-all {slug}, plus cadangan bawaan agar tidak bisa
 * dipakai sebagai nama pendek tautan.
 */
class LegalController extends Controller
{
    /**
     * Slug halaman legal yang otomatis dicadangkan dari pemakaian
     * sebagai nama pendek tautan.
     *
     * @var array<int, string>
     */
    public const SLUG_DICADANGKAN = [
        'tentang-saya',
        'syarat-ketentuan',
        'kebijakan-privasi',
    ];

    public function tentang(): View
    {
        return $this->tampilkan('tentang');
    }

    public function syarat(): View
    {
        return $this->tampilkan('syarat');
    }

    public function privasi(): View
    {
        return $this->tampilkan('privasi');
    }

    private function tampilkan(string $halaman): View
    {
        $tanggal = Carbon::create(2026, 11, 1)->locale(app()->getLocale())->translatedFormat('j F Y');

        return view('publik.legal', [
            'halaman' => $halaman,
            'tanggal' => $tanggal,
            'domain' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: config('app.url'),
        ]);
    }

    /**
     * Gabungkan cadangan bawaan dengan cadangan admin.
     * Dipakai AturanTautanPendek agar slug legal tidak bisa dipakai user.
     *
     * @return array<int, string>
     */
    public static function slugDicadangkan(OperationalSettingsService $layanan): array
    {
        return collect(array_merge(self::SLUG_DICADANGKAN, $layanan->reservedSlugs()))
            ->map(fn ($slug): string => mb_strtolower(trim((string) $slug)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
