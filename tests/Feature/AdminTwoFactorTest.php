<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SecuritySettings;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_can_enable_two_factor(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SecuritySettings::class)
            ->call('mulai')
            ->assertSet('aktif', false)
            ->set('kode', '000000')
            ->call('konfirmasi')
            ->assertHasErrors('kode');

        $secret = (string) session('admin_2fa_setup');
        $this->assertNotSame('', $secret);

        Livewire::actingAs($admin)
            ->test(SecuritySettings::class)
            ->call('mulai')
            ->set('kode', app(TwoFactorService::class)->kodeSaatIni((string) session('admin_2fa_setup')))
            ->call('konfirmasi')
            ->assertHasNoErrors()
            ->assertSet('aktif', true);

        $admin->refresh();

        $this->assertTrue($admin->hasTwoFactor());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.2fa.enable',
        ]);
    }

    public function test_admin_with_two_factor_must_complete_challenge(): void
    {
        $admin = $this->admin();
        $secret = app(TwoFactorService::class)->generateSecret();
        $admin->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect(route('admin.2fa.show'));

        $this->actingAs($admin)
            ->post(route('admin.2fa.verify'), ['code' => app(TwoFactorService::class)->kodeSaatIni($secret)])
            ->assertRedirect('/admin');

        $this->assertTrue(session('admin_2fa_ok'));
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.2fa.success',
        ]);
    }

    public function test_invalid_code_is_rejected_and_audited(): void
    {
        $admin = $this->admin();
        $admin->forceFill([
            'two_factor_secret' => app(TwoFactorService::class)->generateSecret(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->post(route('admin.2fa.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.2fa.failed',
        ]);
    }

    public function test_admin_can_disable_two_factor(): void
    {
        $admin = $this->admin();
        $admin->forceFill([
            'two_factor_secret' => app(TwoFactorService::class)->generateSecret(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        Livewire::actingAs($admin)
            ->test(SecuritySettings::class)
            ->call('nonaktifkan')
            ->assertSet('aktif', false);

        $this->assertFalse($admin->refresh()->hasTwoFactor());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.2fa.disable',
        ]);
    }
}
