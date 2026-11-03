<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Setting\LogPurgeService;
use App\Services\Setting\OperationalSettingsService;
use App\Support\Validation\AturanTautanPendek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminBlocklistDeadPurgeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_domain_diblokir_menolak_tujuan_dan_subdomain(): void
    {
        Setting::set('blocked_domains', ['phishing.test']);

        $ditolak = Validator::make(
            ['destination_url' => 'https://phishing.test/login'],
            AturanTautanPendek::semua(pindaiTujuan: false),
        );

        $this->assertTrue($ditolak->fails());
        $this->assertArrayHasKey('destination_url', $ditolak->errors()->toArray());

        $subdomain = Validator::make(
            ['destination_url' => 'https://sub.phishing.test/x'],
            AturanTautanPendek::semua(pindaiTujuan: false),
        );

        $this->assertTrue($subdomain->fails());

        $aman = Validator::make(
            ['destination_url' => 'https://example.com/aman'],
            AturanTautanPendek::semua(pindaiTujuan: false),
        );

        $this->assertFalse($aman->fails());
    }

    public function test_normalisasi_domain_merapikan_input_admin(): void
    {
        $layanan = app(OperationalSettingsService::class);

        $hasil = $layanan->normalisasiDaftarDomain(" PHISHING.test \n*.contoh.test, https://sub.contoh.test:8080\n");

        $this->assertSame(['phishing.test', 'contoh.test', 'sub.contoh.test'], $hasil);
    }

    public function test_query_tautan_mati_memilih_yang_tepat(): void
    {
        $pemilik = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $matiLama = ShortUrl::factory()->create([
            'owner_id' => $pemilik->id,
            'is_active' => true,
            'total_clicks' => 0,
            'created_at' => now()->subDays(100),
            'last_clicked_at' => null,
            'expires_at' => null,
        ]);

        $sehat = ShortUrl::factory()->create([
            'owner_id' => $pemilik->id,
            'is_active' => true,
            'total_clicks' => 5,
            'created_at' => now()->subDays(100),
            'last_clicked_at' => now()->subDay(),
            'expires_at' => null,
        ]);
        $sehat->clicks()->create(['status' => 'success']);

        $segeraKedaluwarsa = ShortUrl::factory()->create([
            'owner_id' => $pemilik->id,
            'is_active' => true,
            'total_clicks' => 3,
            'created_at' => now()->subDay(),
            'last_clicked_at' => now()->subDay(),
            'expires_at' => now()->addDays(3),
        ]);

        $query = ShortUrl::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where(fn ($sub) => $sub
                    ->where('total_clicks', 0)
                    ->where('created_at', '<', now()->subDays(90))
                    ->whereDoesntHave('clicks', fn ($klik) => $klik->where('status', 'success')))
                ->orWhere(fn ($sub) => $sub
                    ->where('last_clicked_at', '<', now()->subDays(90))
                    ->whereDoesntHave('clicks', fn ($klik) => $klik
                        ->where('status', 'wrong_password')
                        ->where('created_at', '>=', now()->subDays(90))))
                ->orWhereBetween('expires_at', [now(), now()->addDays(7)]));

        $id = $query->pluck('id')->all();

        $this->assertContains($matiLama->id, $id);
        $this->assertContains($segeraKedaluwarsa->id, $id);
        $this->assertNotContains($sehat->id, $id);
    }

    public function test_purge_service_menghitung_dan_membersihkan_sesuai_retensi(): void
    {
        $admin = $this->admin();
        Setting::set('log_retention_days', 90);
        $tautan = ShortUrl::factory()->create(['owner_id' => $admin->id]);

        DB::table('audit_logs')->insert([
            ['actor_id' => $admin->id, 'action' => 'lama', 'created_at' => now()->subDays(100)],
            ['actor_id' => $admin->id, 'action' => 'baru', 'created_at' => now()],
        ]);
        DB::table('click_events')->insert([
            ['short_url_id' => $tautan->id, 'status' => 'success', 'created_at' => now()->subDays(100)],
            ['short_url_id' => $tautan->id, 'status' => 'success', 'created_at' => now()],
        ]);

        $layanan = app(LogPurgeService::class);
        $pratinjau = $layanan->pratinjau();

        $this->assertSame(90, $pratinjau['hari']);
        $this->assertGreaterThanOrEqual(1, $pratinjau['audit']);
        $this->assertGreaterThanOrEqual(1, $pratinjau['klik']);
        $this->assertSame($pratinjau['audit'] + $pratinjau['masuk'] + $pratinjau['klik'] + $pratinjau['peran'], $pratinjau['total']);

        $hasil = $layanan->bersihkan();

        $this->assertSame(90, $hasil['hari']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'lama']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'baru']);
        $this->assertSame(1, DB::table('click_events')->count());
    }
}
