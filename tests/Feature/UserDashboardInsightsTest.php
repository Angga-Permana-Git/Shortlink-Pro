<?php

namespace Tests\Feature;

use App\Filament\User\Pages\UserDashboard;
use App\Models\ClickEvent;
use App\Models\LinktreeEvent;
use App\Models\LinktreeLink;
use App\Models\LinktreeProfile;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Dashboard\UserAktivitasService;
use App\Services\Dashboard\UserDaftarTautanService;
use App\Services\Dashboard\UserKesehatanService;
use App\Services\Dashboard\UserLinktreeStatistikService;
use App\Services\Dashboard\UserStatistikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserDashboardInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tren_membandingkan_periode_dan_menampilkan_aktivitas(): void
    {
        $pengguna = $this->pengguna();
        $tautan = ShortUrl::factory()->create(['owner_id' => $pengguna->id]);

        ClickEvent::factory()->for($tautan)->create([
            'status' => 'success',
            'referer' => 'https://www.google.com/search?q=shortlink',
            'created_at' => now()->subDays(2),
        ]);
        ClickEvent::factory()->for($tautan)->create([
            'status' => 'wrong_password',
            'referer' => 'https://wa.me/628123',
            'created_at' => now()->subDay(),
        ]);
        ClickEvent::factory()->for($tautan)->create([
            'status' => 'success',
            'referer' => null,
            'created_at' => now()->subDays(10),
        ]);

        $tren = app(UserStatistikService::class)->trenKlik($pengguna, 7);
        $this->assertSame(2, $tren['total']);
        $this->assertSame(1, $tren['sebelum']);
        $this->assertSame(7, $tren['hari']);
        $this->assertCount(7, $tren['harian']);

        $aktivitas = app(UserAktivitasService::class);
        $sumber = $aktivitas->sumberKlik($pengguna, 7);
        $this->assertSame('google.com', $sumber[0]['label']);
        $this->assertSame('wa.me', $sumber[1]['label']);

        $kualitas = $aktivitas->kualitas($pengguna, 7);
        $this->assertSame(2, $kualitas['total']);
        $this->assertSame(1, $kualitas['berhasil']);
        $this->assertSame(1, $kualitas['gagal']);
        $this->assertSame(50.0, $kualitas['persen']);
    }

    public function test_dashboard_menampilkan_periode_kesehatan_dan_statistik_linktree(): void
    {
        $pengguna = $this->pengguna();
        ShortUrl::factory()->create([
            'owner_id' => $pengguna->id,
            'expires_at' => now()->addDays(2),
            'total_clicks' => 0,
        ]);

        $profil = LinktreeProfile::create([
            'user_id' => $pengguna->id,
            'display_name' => 'Profil Uji',
            'public_slug' => 'profil-uji',
            'is_active' => true,
        ]);
        $tautanLinktree = LinktreeLink::create([
            'profile_id' => $profil->id,
            'label' => 'Situs',
            'url' => 'https://example.com',
            'is_active' => true,
        ]);
        LinktreeEvent::create(['linktree_profile_id' => $profil->id, 'jenis' => 'kunjungan', 'ip_hash' => 'a']);
        LinktreeEvent::create(['linktree_profile_id' => $profil->id, 'jenis' => 'kunjungan', 'ip_hash' => 'a']);
        LinktreeEvent::create([
            'linktree_profile_id' => $profil->id,
            'linktree_link_id' => $tautanLinktree->id,
            'jenis' => 'klik',
            'ip_hash' => 'a',
        ]);

        Livewire::actingAs($pengguna)
            ->test(UserDashboard::class)
            ->assertOk()
            ->assertSee('Tren Klik 30 Hari')
            ->assertSee('Kesehatan Tautan')
            ->assertSee('Kedaluwarsa ≤7 hari')
            ->assertSee('Statistik Linktree')
            ->assertSee('50%')
            ->call('ubahPeriode', 7)
            ->assertSet('periode', 7)
            ->assertSee('Tren Klik 7 Hari')
            ->call('ubahPeriode', 123)
            ->assertSet('periode', 30)
            ->call('terapkanFilter', 'expiring')
            ->assertSet('filterStatus', 'expiring');
    }

    public function test_kesehatan_dan_filter_tautan_menghasilkan_daftar_yang_sesuai(): void
    {
        $pengguna = $this->pengguna();

        $segera = ShortUrl::factory()->create([
            'owner_id' => $pengguna->id,
            'expires_at' => now()->addDays(3),
            'total_clicks' => 0,
        ]);
        $kedaluwarsa = ShortUrl::factory()->expired()->create(['owner_id' => $pengguna->id]);
        $terjadwal = ShortUrl::factory()->create([
            'owner_id' => $pengguna->id,
            'starts_at' => now()->addDay(),
        ]);

        $kesehatan = app(UserKesehatanService::class);
        $ringkasan = $kesehatan->ringkasan($pengguna);
        $this->assertSame(1, $ringkasan['kedaluwarsa_7']);
        $this->assertSame(1, $ringkasan['sudah_kedaluwarsa']);
        $this->assertSame(1, $ringkasan['terjadwal']);
        $this->assertSame(3, $ringkasan['belum_diklik']);

        $judul = array_column($kesehatan->daftar($pengguna), 'judul');
        $this->assertContains('Kedaluwarsa ≤7 hari', $judul);
        $this->assertContains('Sudah kedaluwarsa', $judul);
        $this->assertContains('Terjadwal', $judul);
        $this->assertContains('Belum pernah diklik', $judul);

        $daftar = app(UserDaftarTautanService::class);
        $this->assertSame([$segera->slug], array_column($daftar->daftar($pengguna, '', 'expiring'), 'slug'));
        $this->assertSame([$kedaluwarsa->slug], array_column($daftar->daftar($pengguna, '', 'expired'), 'slug'));
        $this->assertSame([$terjadwal->slug], array_column($daftar->daftar($pengguna, '', 'scheduled'), 'slug'));
        $this->assertCount(3, $daftar->daftar($pengguna, '', 'never'));
    }

    public function test_statistik_linktree_menghitung_kunjungan_pengunjung_dan_ctr(): void
    {
        $pengguna = $this->pengguna();
        $profil = LinktreeProfile::create([
            'user_id' => $pengguna->id,
            'display_name' => 'Profil Uji',
            'public_slug' => 'profil-uji',
            'is_active' => true,
        ]);
        $tautan = LinktreeLink::create([
            'profile_id' => $profil->id,
            'label' => 'Situs',
            'url' => 'https://example.com',
            'is_active' => true,
        ]);

        LinktreeEvent::create(['linktree_profile_id' => $profil->id, 'jenis' => 'kunjungan', 'ip_hash' => 'a']);
        LinktreeEvent::create(['linktree_profile_id' => $profil->id, 'jenis' => 'kunjungan', 'ip_hash' => 'b']);
        LinktreeEvent::create([
            'linktree_profile_id' => $profil->id,
            'linktree_link_id' => $tautan->id,
            'jenis' => 'klik',
            'ip_hash' => 'a',
        ]);

        $statistik = app(UserLinktreeStatistikService::class)->ringkasan($pengguna);
        $this->assertSame(2, $statistik['kunjungan']);
        $this->assertSame(2, $statistik['pengunjung']);
        $this->assertSame(1, $statistik['klik']);
        $this->assertSame(50.0, $statistik['ctr']);
        $this->assertSame('Situs', $statistik['tautan'][0]['label']);
    }

    private function pengguna(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);
    }
}
