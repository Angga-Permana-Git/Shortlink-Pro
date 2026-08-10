<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\TimezoneSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\System\TimezoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimezoneSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_timezone_page_loads_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin/timezone-settings')
            ->assertOk();
    }

    public function test_admin_can_save_timezone_and_audit_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(TimezoneSettings::class)
            ->fillForm(['timezone' => 'Asia/Makassar'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Asia/Makassar', Setting::get(TimezoneService::SETTING_KEY));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'settings.timezone.update',
        ]);
    }

    public function test_default_timezone_is_from_config(): void
    {
        $tz = app(TimezoneService::class)->get();

        $this->assertSame(config('app.timezone'), $tz);
    }

    public function test_invalid_timezone_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(TimezoneSettings::class)
            ->fillForm(['timezone' => 'Not/AZone'])
            ->call('save');

        $this->assertNull(Setting::get(TimezoneService::SETTING_KEY));
    }
}