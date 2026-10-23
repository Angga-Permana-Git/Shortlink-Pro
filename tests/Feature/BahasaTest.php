<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pemilih bahasa di beranda dan isolasi pilihan bahasa antar pengguna.
 */
class BahasaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_uses_selected_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->from('/admin')
            ->post(route('bahasa.ganti'), ['locale' => 'en'])
            ->assertRedirect('/admin');

        $this->assertSame('en', $admin->fresh()->locale);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Dasbor')
            ->assertSee('Needs Attention')
            ->assertSee('User Ranking')
            ->assertSee('Operational Settings')
            ->assertDontSee('Perlu Perhatian')
            ->assertSee('aria-label="Pilih bahasa"', false)
            ->assertSee('flex-direction: row', false);

        $this->get('/admin/users')
            ->assertOk()
            ->assertSee('Send Password Reset')
            ->assertDontSee('Kirim Reset');
    }

    public function test_user_panel_uses_selected_language(): void
    {
        $pengguna = User::factory()->create(['is_active' => true]);

        $this->actingAs($pengguna)
            ->from('/user')
            ->post(route('bahasa.ganti'), ['locale' => 'en'])
            ->assertRedirect('/user');

        $this->get('/user')
            ->assertOk()
            ->assertSee('Sign out')
            ->assertSee('My Dashboard');
    }

    public function test_home_page_renders_language_switcher(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('action="'.route('bahasa.ganti').'"', false)
            ->assertSee('English');
    }

    public function test_guest_can_switch_language_to_english(): void
    {
        $this->post(route('bahasa.ganti'), ['locale' => 'en'])
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');

        $this->get('/')->assertOk()->assertSee('Free Link Shortener');
    }

    public function test_guest_choice_does_not_change_other_visitors(): void
    {
        $this->post(route('bahasa.ganti'), ['locale' => 'en'])
            ->assertSessionHas('locale', 'en');

        // Peramban lain tidak membawa sesi ini, jadi tetap memakai bahasa bawaan.
        $this->flushSession();

        $this->get('/')->assertOk()->assertSee('Pendekkan Tautan Anda');
    }

    public function test_language_choice_of_a_user_is_saved_only_for_that_user(): void
    {
        $penggunaA = User::factory()->create();
        $penggunaB = User::factory()->create();

        $this->actingAs($penggunaA)
            ->post(route('bahasa.ganti'), ['locale' => 'en'])
            ->assertRedirect();

        $this->assertSame('en', $penggunaA->fresh()->locale);
        $this->assertNull($penggunaB->fresh()->locale);
    }

    public function test_language_choice_of_admin_and_user_are_independent(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pengguna = User::factory()->create();

        $this->actingAs($pengguna)->post(route('bahasa.ganti'), ['locale' => 'en']);

        $this->assertNull($admin->fresh()->locale, 'Pilihan pengguna tidak boleh mengubah bahasa admin.');

        $this->actingAs($admin)->post(route('bahasa.ganti'), ['locale' => 'id']);

        $this->assertSame('id', $admin->fresh()->locale);
        $this->assertSame('en', $pengguna->fresh()->locale, 'Pilihan admin tidak boleh mengubah bahasa pengguna.');
    }

    public function test_language_change_is_recorded_in_audit_log(): void
    {
        $pengguna = User::factory()->create();

        $this->actingAs($pengguna)->post(route('bahasa.ganti'), ['locale' => 'en']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'locale.update',
            'resource_type' => 'user',
            'resource_id' => $pengguna->getKey(),
        ]);
    }

    public function test_unsupported_language_is_rejected(): void
    {
        $this->post(route('bahasa.ganti'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->assertNull(session('locale'));
    }
}
