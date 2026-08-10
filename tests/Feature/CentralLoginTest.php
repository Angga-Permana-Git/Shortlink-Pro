<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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