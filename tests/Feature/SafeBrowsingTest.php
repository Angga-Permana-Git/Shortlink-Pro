<?php

namespace Tests\Feature;

use App\Support\Validation\AturanTautanPendek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Pemeriksaan tautan tujuan ke Google Safe Browsing (config/pemendek.php
 * bagian `safe_browsing`). Layanan luar selalu dipalsukan; tidak ada
 * permintaan jaringan sungguhan.
 */
class SafeBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private const ALAMAT_LAYANAN = '*safebrowsing.googleapis.com*';

    private function aktifkanPemeriksaan(): void
    {
        config([
            'pemendek.safe_browsing.enabled' => true,
            'pemendek.safe_browsing.api_key' => 'kunci-uji',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function jawabanBerbahaya(string $tipe = 'SOCIAL_ENGINEERING'): array
    {
        return [
            'matches' => [
                [
                    'threatType' => $tipe,
                    'platformType' => 'ANY_PLATFORM',
                    'threat' => ['url' => 'http://phishing.test/'],
                    'cacheDuration' => '300s',
                ],
            ],
        ];
    }

    public function test_tautan_aman_tetap_bisa_dipendekkan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => Http::response([], 200)]);

        $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com/aman',
        ])->assertCreated();

        $this->assertDatabaseHas('short_urls', ['destination_url' => 'https://example.com/aman']);
    }

    public function test_permintaan_pemeriksaan_memuat_alamat_tujuan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => Http::response([], 200)]);

        $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com/diperiksa',
        ])->assertCreated();

        Http::assertSent(function ($request): bool {
            return $request->url() === config('pemendek.safe_browsing.endpoint').'?key=kunci-uji'
                && $request['threatInfo']['threatEntries'][0]['url'] === 'https://example.com/diperiksa'
                && in_array('MALWARE', $request['threatInfo']['threatTypes'], true);
        });
    }

    public function test_tautan_berbahaya_ditolak_dan_tidak_tersimpan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => Http::response($this->jawabanBerbahaya(), 200)]);

        $response = $this->postJson(route('shorten.public'), [
            'destination_url' => 'http://phishing.test/',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('destination_url');

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_pemeriksaan_dilewati_bila_kunci_api_kosong(): void
    {
        config([
            'pemendek.safe_browsing.enabled' => true,
            'pemendek.safe_browsing.api_key' => '',
        ]);
        Http::fake();
        Http::preventStrayRequests();

        $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com/tanpa-kunci',
        ])->assertCreated();

        Http::assertNothingSent();
    }

    public function test_layanan_galat_tidak_menghalangi_pemendekan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => Http::response(['error' => 'galat'], 500)]);

        $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com/saat-layanan-galat',
        ])->assertCreated();

        $this->assertDatabaseHas('short_urls', ['destination_url' => 'https://example.com/saat-layanan-galat']);
    }

    public function test_koneksi_timeout_tidak_menghalangi_pemendekan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => fn () => throw new ConnectionException('Waktu habis.')]);

        $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com/saat-timeout',
        ])->assertCreated();

        $this->assertDatabaseHas('short_urls', ['destination_url' => 'https://example.com/saat-timeout']);
    }

    /**
     * Tautan yang sudah aman tidak boleh bisa dialihkan ke alamat berbahaya
     * lewat formulir perubahan.
     */
    public function test_perubahan_tujuan_ikut_diperiksa(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake([self::ALAMAT_LAYANAN => Http::response($this->jawabanBerbahaya(), 200)]);

        $this->expectException(ValidationException::class);

        Validator::make(
            ['destination_url' => 'http://phishing.test/'],
            AturanTautanPendek::perubahan(),
        )->validate();
    }

    /**
     * Impor CSV bisa berisi ribuan baris, jadi pemeriksaan per baris dimatikan
     * pemanggilnya (AdminShortUrlImportService).
     */
    public function test_aturan_tanpa_pemeriksaan_tidak_memanggil_layanan(): void
    {
        $this->aktifkanPemeriksaan();
        Http::fake();
        Http::preventStrayRequests();

        Validator::make(
            ['destination_url' => 'https://example.com/impor'],
            AturanTautanPendek::semua(pindaiTujuan: false),
        )->validate();

        Http::assertNothingSent();
    }
}
