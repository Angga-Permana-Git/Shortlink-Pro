<?php

namespace Tests\Feature;

use App\Filament\User\Pages\UserDashboard;
use App\Models\ShortUrl;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_has_server_clock_and_timezone_page_is_removed(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('data-hook="jam-server"', false)
            ->assertSee(config('app.timezone'));

        $this->actingAs($admin)
            ->get('/admin/timezone-settings')
            ->assertNotFound();
    }

    public function test_user_timezone_is_used_when_creating_a_link(): void
    {
        $pengguna = User::factory()->create(['timezone' => 'America/New_York']);
        $perkiraan = Carbon::parse('2030-01-02 20:00', 'America/New_York')
            ->setTimezone((string) config('app.timezone'))
            ->format('Y-m-d H:i:s');

        Livewire::actingAs($pengguna)
            ->test(UserDashboard::class)
            ->set('data.destination_url', 'https://contoh.com/kirim')
            ->set('data.expires_at', '2030-01-02T20:00')
            ->call('buatTautan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('short_urls', [
            'owner_id' => $pengguna->id,
            'expires_at' => $perkiraan,
        ]);
    }

    public function test_user_can_choose_timezone_and_change_is_audited(): void
    {
        $pengguna = User::factory()->create(['timezone' => null]);

        Livewire::actingAs($pengguna)
            ->test(UserDashboard::class)
            ->set('zonaWaktu', 'Asia/Jayapura')
            ->call('simpanZonaWaktu')
            ->assertHasNoErrors();

        $this->assertSame('Asia/Jayapura', $pengguna->fresh()->timezone);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $pengguna->id,
            'action' => 'user.update',
        ]);
    }

    public function test_admin_sees_foreign_links_with_local_and_server_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pengguna = User::factory()->create([
            'name' => 'Pengguna New York',
            'timezone' => 'America/New_York',
        ]);
        $tautan = ShortUrl::create([
            'owner_id' => $pengguna->id,
            'slug' => 'lintas-zona',
            'destination_url' => 'https://contoh.com/lintas',
            'is_active' => true,
            'expires_at' => '2030-01-03 08:00:00',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Tautan Pengguna Lintas Zona Waktu')
            ->assertSee('Pengguna New York')
            ->assertSee('America/New_York')
            ->assertSee('Server:')
            ->assertSee('lintas-zona');
    }
}
