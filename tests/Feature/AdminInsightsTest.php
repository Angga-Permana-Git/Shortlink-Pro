<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Filament\Widgets\ActiveUsersTable;
use App\Filament\Widgets\AdminAttentionWidget;
use App\Filament\Widgets\AdminUserHealthTable;
use App\Filament\Widgets\ClickHeatmapWidget;
use App\Filament\Widgets\ClicksPerDayChart;
use App\Filament\Widgets\DashboardPeriodFilter;
use App\Filament\Widgets\DestinationDomainsWidget;
use App\Filament\Widgets\RecentActivityTable;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SystemStatusWidget;
use App\Models\ClickEvent;
use App\Models\LinktreeProfile;
use App\Models\LoginAuditLog;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminAttentionService;
use App\Services\Admin\AdminDashboardService;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ringkasan_periode_analitik_dan_tindakan_admin(): void
    {
        $admin = $this->admin();
        $baru = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'timezone' => 'America/New_York',
            'created_at' => now()->subDays(10),
        ]);
        $nonaktif = User::factory()->create([
            'role' => 'user',
            'is_active' => false,
            'timezone' => 'Asia/Jakarta',
        ]);
        $tautan = ShortUrl::factory()->create([
            'owner_id' => $nonaktif->id,
            'destination_url' => 'https://www.google.com/halaman',
            'total_clicks' => 7,
        ]);
        ShortUrl::factory()->create([
            'owner_id' => $admin->id,
            'destination_url' => 'https://wa.me/628123',
        ]);
        ClickEvent::factory()->for($tautan)->create([
            'status' => 'wrong_password',
            'created_at' => now()->subHour(),
        ]);
        LoginAuditLog::create([
            'user_id' => $baru->id,
            'username' => $baru->username,
            'auth_provider' => 'local',
            'status' => 'failed',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);
        LinktreeProfile::create([
            'user_id' => $baru->id,
            'display_name' => 'Tanpa Alamat',
            'is_active' => true,
        ]);
        AuditLogger::log($admin, 'user.create', 'user', $baru->id);

        $ringkasan = app(AdminDashboardService::class)->ringkasan(7);
        $this->assertArrayHasKey('tautan_baru_sebelum', $ringkasan);
        $this->assertArrayHasKey('klik_periode', $ringkasan);
        $this->assertSame(7, $ringkasan['klik']);

        $analitik = app(AdminAnalyticsService::class);
        $domain = $analitik->domainTujuan();
        $this->assertSame('google.com', $domain[0]['domain']);
        $this->assertSame(7, $domain[0]['klik']);

        $peta = $analitik->heatmap(7);
        $this->assertCount(7, $peta['grid']);
        $this->assertCount(24, $peta['grid'][0]);

        $perlu = $analitik->penggunaPerluPerhatianQuery()->pluck('id')->all();
        $this->assertContains($baru->id, $perlu);
        $this->assertContains($nonaktif->id, $perlu);

        $judul = array_column(app(AdminAttentionService::class)->peringatan(), 'judul');
        $this->assertContains('Admin tanpa 2FA', $judul);
        $this->assertContains('Pengguna baru belum membuat tautan', $judul);
        $this->assertContains('Pengguna nonaktif punya tautan aktif', $judul);
        $this->assertContains('Login gagal berulang', $judul);
        $this->assertContains('Profil Linktree tanpa alamat', $judul);
        $this->assertContains('Tautan dengan klik gagal', $judul);
    }

    public function test_widget_dan_filter_dashboard_admin_dirender(): void
    {
        $admin = $this->admin();
        ShortUrl::factory()->count(2)->create(['owner_id' => $admin->id]);

        Livewire::actingAs($admin)->test(StatsOverview::class)->assertOk();
        Livewire::actingAs($admin)
            ->test(AdminAttentionWidget::class)
            ->assertOk()
            ->assertSee('Perlu Perhatian');
        Livewire::actingAs($admin)
            ->test(RecentActivityTable::class)
            ->assertOk()
            ->assertSee('Aktivitas Terbaru');
        Livewire::actingAs($admin)
            ->test(AdminUserHealthTable::class)
            ->assertOk()
            ->assertSee('Kesehatan Pengguna');
        Livewire::actingAs($admin)
            ->test(ActiveUsersTable::class)
            ->assertOk()
            ->assertSee('Peringkat Pengguna')
            ->assertSee('Klik Gagal');
        Livewire::actingAs($admin)
            ->test(DestinationDomainsWidget::class)
            ->assertOk()
            ->assertSee('Domain Tujuan');
        Livewire::actingAs($admin)
            ->test(ClickHeatmapWidget::class)
            ->assertOk()
            ->assertSee('Peta Klik');
        Livewire::actingAs($admin)
            ->test(SystemStatusWidget::class)
            ->assertOk()
            ->assertSee('Penjadwal');
        Livewire::actingAs($admin)
            ->test(DashboardPeriodFilter::class)
            ->assertOk()
            ->assertSee('30 hari');

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableFilterExists('tanpa_2fa')
            ->assertTableFilterExists('tanpa_tautan')
            ->assertTableFilterExists('nonaktif_bertantau');
    }

    public function test_filter_periode_memengaruhi_grafik_dan_halaman_admin(): void
    {
        $admin = $this->admin();

        Livewire::withQueryParams(['periode' => 7])
            ->actingAs($admin)
            ->test(ClicksPerDayChart::class)
            ->assertOk()
            ->assertSee('Klik Sukses · 7 Hari');

        $this->actingAs($admin)
            ->get('/admin?periode=7')
            ->assertOk()
            ->assertSee('Klik Sukses · 7 Hari')
            ->assertSee('Aktivitas Terbaru')
            ->assertSee('Kesehatan Pengguna')
            ->assertSee('Domain Tujuan')
            ->assertSee('Peta Klik');
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);
    }
}
