<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class CentralLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_guest_sees_public_home_when_force_sso_is_off(): void
    {
        Setting::set('force_sso', false);

        $this->get('/')->assertOk()->assertSee('Pendekkan Tautan Anda');
    }

    public function test_guest_is_redirected_to_keycloak_when_force_sso_is_on(): void
    {
        Setting::set('force_sso', true);

        $this->get('/')->assertRedirect(route('keycloak.redirect'));
    }

    public function test_login_page_shows_keycloak_button_when_force_sso_is_on(): void
    {
        config([
            'services.keycloak.base_url' => 'https://sso.test',
            'services.keycloak.realms' => 'internal',
        ]);
        Setting::set('force_sso', true);

        $this->get('/login')->assertOk()->assertSee('Login dengan SSO');
    }

    public function test_login_page_sends_guest_to_local_form_when_force_sso_is_off(): void
    {
        Setting::set('force_sso', false);

        $this->get('/login')->assertRedirect(route('login.local'));
    }

    public function test_redirect_keycloak_mengantar_ke_halaman_keycloak(): void
    {
        config([
            'services.keycloak.base_url' => 'https://sso.test',
            'services.keycloak.realms' => 'internal',
        ]);

        $driver = Mockery::mock(SocialiteProvider::class);
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('https://sso.test/auth'));
        Socialite::shouldReceive('driver')->with('keycloak')->andReturn($driver);

        $this->get('/auth/keycloak/redirect')->assertRedirect('https://sso.test/auth');
    }

    public function test_public_home_does_not_offer_login_buttons_on_card(): void
    {
        Setting::set('force_sso', false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Lanjutkan dengan Keycloak')
            ->assertDontSee('Daftar Manual');
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        User::factory()->create([
            'email' => 'admin@test.local',
            'username' => 'admin@test.local',
            'password' => 'password',
            'auth_provider' => 'local',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login/local', [
            'username' => 'admin@test.local',
            'password' => 'password',
        ])->assertRedirect('/admin');
    }

    public function test_user_login_redirects_to_user_panel(): void
    {
        User::factory()->create([
            'email' => 'user@test.local',
            'username' => 'user@test.local',
            'password' => 'password',
            'auth_provider' => 'local',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->post('/login/local', [
            'username' => 'user@test.local',
            'password' => 'password',
        ])->assertRedirect('/user');
    }

    public function test_login_by_username_works(): void
    {
        User::factory()->create([
            'email' => 'budi@test.local',
            'username' => 'budi',
            'password' => 'password',
            'auth_provider' => 'local',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->post('/login/local', [
            'username' => 'budi',
            'password' => 'password',
        ])->assertRedirect('/user');
    }

    public function test_invalid_credentials_fail_with_rate_limit_keys(): void
    {
        $this->post('/login/local', [
            'username' => 'nobody@test.local',
            'password' => 'wrong',
        ])->assertSessionHasErrors('username');
    }

    public function test_non_local_account_cannot_login_locally(): void
    {
        User::factory()->create([
            'email' => 'sso@test.local',
            'username' => 'sso',
            'password' => 'password',
            'auth_provider' => 'keycloak',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->post('/login/local', [
            'username' => 'sso@test.local',
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_logout_returns_to_login_page(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.local',
            'username' => 'user@test.local',
            'password' => 'password',
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
