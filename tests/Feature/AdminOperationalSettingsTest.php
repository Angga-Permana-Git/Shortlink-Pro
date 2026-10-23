<?php

namespace Tests\Feature;

use App\Dto\CreateShortUrlDTO;
use App\Filament\Admin\Pages\OperationalSettings;
use App\Models\Setting;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Setting\OperationalSettingsService;
use App\Services\ShortUrl\ShortUrlService;
use App\Support\Validation\AturanTautanPendek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class AdminOperationalSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_can_save_operational_settings_and_audit_is_recorded(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OperationalSettings::class)
            ->fillForm([
                'reserved_slugs' => "admin\nlogin, promo",
                'default_expiry_days' => 7,
                'log_retention_days' => 30,
                'rate_limit_per_minute' => 15,
                'announcement_banner' => 'Pemeliharaan malam ini pukul 22.00.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $layanan = app(OperationalSettingsService::class);

        $this->assertSame(['admin', 'login', 'promo'], $layanan->reservedSlugs());
        $this->assertSame(7, $layanan->defaultExpiryDays());
        $this->assertSame(30, $layanan->logRetentionDays());
        $this->assertSame(15, $layanan->rateLimitPerMinute());
        $this->assertSame('Pemeliharaan malam ini pukul 22.00.', $layanan->announcementBanner());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'settings.operational.update',
        ]);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        Setting::set('reserved_slugs', ['promo']);

        $validator = Validator::make([
            'destination_url' => 'https://example.com',
            'slug' => 'Promo',
        ], AturanTautanPendek::semua());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    public function test_default_expiry_is_applied_to_new_links(): void
    {
        Setting::set('default_expiry_days', 7);
        $pemilik = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $tautan = app(ShortUrlService::class)->create($pemilik, new CreateShortUrlDTO(
            destinationUrl: 'https://example.com',
        ));

        $this->assertNotNull($tautan->expires_at);
        $this->assertTrue($tautan->expires_at->between(now()->addDays(6), now()->addDays(8)));
        $this->assertSame(1, ShortUrl::count());
    }

    public function test_announcement_banner_is_shown_on_user_panel(): void
    {
        Setting::set('announcement_banner', 'Pemeliharaan malam ini.');
        $pengguna = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->actingAs($pengguna)
            ->get('/user')
            ->assertOk()
            ->assertSee('Pemeliharaan malam ini.');
    }
}
