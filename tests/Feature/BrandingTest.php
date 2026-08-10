<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\BrandingSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_branding_page_loads_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin/branding-settings')
            ->assertOk();
    }

    public function test_admin_can_save_branding_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(BrandingSettings::class)
            ->fillForm([
                'app_name' => 'PMK Shortlink',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('PMK Shortlink', Setting::get('app_name'));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'branding.update',
        ]);
    }

    public function test_login_logo_url_returns_correct_asset_url(): void
    {
        Setting::set('login_logo', 'branding/test-logo.png');

        $logoUrl = app(\App\Services\Branding\BrandingService::class)->loginLogoUrl();

        $this->assertStringContainsString('/storage/branding/test-logo.png', $logoUrl);
    }

    public function test_saving_branding_preserves_existing_logo(): void
    {
        Setting::set('login_logo', 'branding/existing-logo.png');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(BrandingSettings::class)
            ->fillForm([
                'app_name' => 'Updated App Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('branding/existing-logo.png', Setting::get('login_logo'));
    }

    public function test_admin_can_delete_logo_cleanly(): void
    {
        Setting::set('login_logo', 'branding/logo-to-delete.png');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(BrandingSettings::class)
            ->call('deleteLogo')
            ->assertHasNoFormErrors();

        $this->assertNull(Setting::get('login_logo'));
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'branding.logo_delete',
        ]);
    }
}