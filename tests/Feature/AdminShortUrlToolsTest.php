<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ShortUrlResource\Pages\ListShortUrls;
use App\Filament\Admin\Resources\ShortUrlResource\Pages\ViewShortUrl;
use App\Filament\Admin\Resources\ShortUrlResource\RelationManagers\ClickEventsRelationManager;
use App\Filament\Widgets\StatsOverview;
use App\Models\AuditLog;
use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminShortUrlToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);
    }

    public function test_stats_overview_shows_all_dashboard_metrics(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertOk()
            ->assertSee('Pengguna Aktif')
            ->assertSee('Pengguna Baru')
            ->assertSee('Total Tautan')
            ->assertSee('Tautan Aktif')
            ->assertSee('Total Klik')
            ->assertSee('Klik 30 Hari')
            ->assertSee('Linktree Terbit')
            ->assertSee('Profil Tanpa Alamat');
    }

    public function test_admin_can_bulk_deactivate_links_with_audit_trail(): void
    {
        $admin = $this->admin();
        $tautan = ShortUrl::factory()->count(2)->create(['owner_id' => $admin->id, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(ListShortUrls::class)
            ->callTableBulkAction('nonaktifkan', $tautan->pluck('id')->all())
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseCount('short_urls', 2);
        $this->assertSame(0, ShortUrl::where('is_active', true)->count());
        $this->assertSame(2, AuditLog::where('action', 'link.update')->count());
    }

    public function test_admin_can_reassign_link_owner_in_bulk(): void
    {
        $admin = $this->admin();
        $pemilikBaru = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $tautan = ShortUrl::factory()->create(['owner_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(ListShortUrls::class)
            ->callTableBulkAction('pindahkan_pemilik', [$tautan->id], data: ['owner_id' => $pemilikBaru->id])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame($pemilikBaru->id, $tautan->refresh()->owner_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'link.update',
            'resource_id' => $tautan->id,
        ]);
    }

    public function test_short_link_detail_shows_analytics_and_click_history(): void
    {
        $admin = $this->admin();
        $tautan = ShortUrl::factory()->create(['owner_id' => $admin->id, 'total_clicks' => 1]);
        ClickEvent::create([
            'short_url_id' => $tautan->id,
            'status' => 'success',
            'ip_hash' => hash('sha256', '127.0.0.1'),
        ]);

        Livewire::actingAs($admin)
            ->test(ViewShortUrl::class, ['record' => $tautan->id])
            ->assertOk()
            ->assertSee('Informasi Tautan')
            ->assertSee('Analitik Klik')
            ->assertSee('Klik unik');

        Livewire::actingAs($admin)
            ->test(ClickEventsRelationManager::class, [
                'ownerRecord' => $tautan,
                'pageClass' => ViewShortUrl::class,
            ])
            ->assertOk()
            ->assertSee('Riwayat Klik')
            ->assertSee('Berhasil');
    }
}
