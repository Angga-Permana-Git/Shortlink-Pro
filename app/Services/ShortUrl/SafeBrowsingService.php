<?php

namespace App\Services\ShortUrl;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Memeriksa tautan tujuan ke Google Safe Browsing (Lookup API v4).
 *
 * Dipanggil aturan validasi `TautanAman` supaya tautan berbahaya ditolak
 * sebelum tersimpan. Kegagalan layanan tidak menghentikan pemendekan:
 * permintaan diteruskan dan kegagalan dicatat sebagai peringatan. Alamat
 * tujuan tidak ditulis utuh ke log karena bisa memuat token; hanya host.
 */
class SafeBrowsingService
{
    /**
     * Tipe ancaman yang diperiksa. Urutan ini juga menentukan tipe yang
     * dilaporkan bila Google mengembalikan lebih dari satu.
     */
    private const TIPE_ANCAMAN = [
        'MALWARE',
        'SOCIAL_ENGINEERING',
        'UNWANTED_SOFTWARE',
        'POTENTIALLY_HARMFUL_APPLICATION',
    ];

    /**
     * Pemeriksaan hanya berjalan bila fitur diaktifkan dan kunci API terisi,
     * sehingga pengembangan tanpa kunci tetap bisa memendekkan tautan.
     */
    public function aktif(): bool
    {
        return (bool) config('pemendek.safe_browsing.enabled')
            && trim((string) config('pemendek.safe_browsing.api_key')) !== '';
    }

    /**
     * Nama tipe ancaman bila tautan terdaftar berbahaya, atau null bila aman,
     * tidak diperiksa, atau layanan gagal dihubungi.
     */
    public function periksa(string $url): ?string
    {
        if (! $this->aktif()) {
            return null;
        }

        try {
            $response = Http::timeout($this->batasDetik())
                ->acceptJson()
                ->post($this->alamatEndpoint(), [
                    'client' => [
                        'clientId' => 'shortlink-pro',
                        'clientVersion' => '1.0.0',
                    ],
                    'threatInfo' => [
                        'threatTypes' => self::TIPE_ANCAMAN,
                        'platformTypes' => ['ANY_PLATFORM'],
                        'threatEntryTypes' => ['URL'],
                        'threatEntries' => [['url' => $url]],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            $this->catatKegagalan($url, $exception->getMessage());

            return null;
        }

        if ($response->failed()) {
            $this->catatKegagalan($url, 'HTTP '.$response->status());

            return null;
        }

        $tipe = $response->json('matches.0.threatType');

        return is_string($tipe) ? $tipe : null;
    }

    private function alamatEndpoint(): string
    {
        return (string) config('pemendek.safe_browsing.endpoint')
            .'?key='.urlencode((string) config('pemendek.safe_browsing.api_key'));
    }

    private function batasDetik(): int
    {
        return max(1, (int) config('pemendek.safe_browsing.timeout_seconds'));
    }

    private function catatKegagalan(string $url, string $sebab): void
    {
        Log::warning('Pemeriksaan Safe Browsing gagal; tautan diteruskan tanpa diperiksa.', [
            'sebab' => $sebab,
            'host' => parse_url($url, PHP_URL_HOST),
        ]);
    }
}
