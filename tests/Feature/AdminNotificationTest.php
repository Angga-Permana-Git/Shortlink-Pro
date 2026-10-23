<?php

namespace Tests\Feature;

use App\Filament\Widgets\AdminAttentionWidget;
use App\Models\ClickEvent;
use App\Models\LinktreeProfile;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attention_widget_lists_actionable_alerts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pemilik = User::factory()->create(['role' => 'user', 'is_active' => true]);

        LinktreeProfile::create([
            'user_id' => $pemilik->id,
            'display_name' => 'Tanpa Alamat',
            'is_active' => true,
        ]);

        $tautan = ShortUrl::create([
            'owner_id' => $pemilik->id,
            'slug' => 'segera-habis',
            'destination_url' => 'https://example.com',
            'is_active' => true,
            'expires_at' => now()->addHours(2),
        ]);

        ClickEvent::create([
            'short_url_id' => $tautan->id,
            'status' => 'wrong_password',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminAttentionWidget::class)
            ->assertSee('Perlu Perhatian')
            ->assertSee('Profil Linktree tanpa alamat')
            ->assertSee('Tautan segera kedaluwarsa')
            ->assertSee('Percobaan kata sandi salah');
    }

    public function test_command_sends_database_notifications_once_per_period(): void
    {
        Cache::flush();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pemilik = User::factory()->create(['role' => 'user', 'is_active' => true]);

        LinktreeProfile::create([
            'user_id' => $pemilik->id,
            'display_name' => 'Tanpa Alamat',
            'is_active' => true,
        ]);

        $this->artisan('admin:notify-attention')->assertSuccessful();
        $jumlah = DB::table('notifications')->where('notifiable_id', $admin->id)->count();

        $this->assertGreaterThan(0, $jumlah);

        $this->artisan('admin:notify-attention')->assertSuccessful();
        $this->assertSame($jumlah, DB::table('notifications')->where('notifiable_id', $admin->id)->count());
    }
}
