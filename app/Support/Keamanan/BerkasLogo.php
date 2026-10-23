<?php

namespace App\Support\Keamanan;

use Illuminate\Http\UploadedFile;

/**
 * Pemeriksaan berkas logo milik pengguna.
 *
 * Ekstensi ditentukan dari isi berkas, bukan dari nama kiriman peramban, supaya
 * berkas yang disamarkan (misalnya berkas PHP yang diberi nama .png) tetap
 * ditolak. SVG diperiksa terpisah karena bisa memuat skrip yang berjalan saat
 * gambarnya dibuka.
 */
final class BerkasLogo
{
    /**
     * MIME yang diterima beserta ekstensi simpan yang aman.
     *
     * @var array<string, string>
     */
    private const EKSTENSI = [
        'image/svg+xml' => 'svg',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
    ];

    /**
     * SVG tanpa prolog XML bisa dikenali sebagai teks biasa oleh pemeriksa MIME.
     *
     * @var array<int, string>
     */
    private const MIME_SVG = ['text/plain', 'text/html', 'application/xml', 'text/xml'];

    /**
     * Konstruksi SVG yang bisa menjalankan kode atau memuat sumber luar.
     *
     * @var array<int, string>
     */
    private const POLA_BERBAHAYA = [
        '<script', '<foreignobject', '<iframe', '<embed', '<object',
        '<handler', '<!entity', 'javascript:', 'data:text/html',
    ];

    /**
     * Ekstensi simpan yang sah, atau null bila berkas bukan logo yang diterima.
     */
    public function ekstensi(UploadedFile $berkas): ?string
    {
        if (! $berkas->isValid()) {
            return null;
        }

        $mime = (string) $berkas->getMimeType();
        $nama = strtolower((string) $berkas->getClientOriginalExtension());

        if ($mime === 'image/svg+xml' || ($nama === 'svg' && in_array($mime, self::MIME_SVG, true))) {
            return 'svg';
        }

        return self::EKSTENSI[$mime] ?? null;
    }

    /**
     * SVG hanya boleh berisi gambar: tanpa skrip, penangan kejadian, atau entitas.
     */
    public function svgAman(UploadedFile $berkas): bool
    {
        $isi = (string) file_get_contents((string) $berkas->getRealPath());
        $kecil = strtolower($isi);

        if ($isi === '' || ! str_contains($kecil, '<svg')) {
            return false;
        }

        foreach (self::POLA_BERBAHAYA as $pola) {
            if (str_contains($kecil, $pola)) {
                return false;
            }
        }

        return preg_match('/\son[a-z]+\s*=/i', $isi) !== 1;
    }
}
