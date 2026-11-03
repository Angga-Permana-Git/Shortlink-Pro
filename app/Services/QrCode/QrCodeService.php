<?php

namespace App\Services\QrCode;

use App\Models\ShortUrl;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Pembuat QR Code sisi server.
 *
 * Dipakai halaman depan untuk menampilkan QR dari tautan pendek yang baru dibuat.
 * Format keluaran SVG agar tidak memerlukan ekstensi GD dan aman untuk gambar vektor.
 */
class QrCodeService
{
    public function svg(string $data): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'svgAddXmlHeader' => false,
            'outputBase64' => false,
        ]);

        return (string) (new QRCode($options))->render($data);
    }

    /**
     * Membuat SVG QR untuk tautan pendek berdasarkan slug.
     *
     * Pencarian data dilakukan di lapisan service agar controller tetap tipis
     * (lihat disiplin_struktur.md bagian 1).
     */
    public function svgUntukSlug(string $slug): string
    {
        $shortUrl = ShortUrl::query()->where('slug', $slug)->firstOrFail();

        // QR tidak membocorkan keberadaan tautan nonaktif/kedaluwarsa/belum
        // mulai; pengunjung tetap melihat 404 seperti membuka slug langsung.
        // Tautan ber-kata sandi tetap boleh: QR mengarah ke formulir sandi.
        if (! $shortUrl->is_active || $shortUrl->isExpired() || $shortUrl->notStarted()) {
            throw new ModelNotFoundException;
        }

        return $this->svg($shortUrl->shortUrl());
    }
}
