<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function konfigurasiGoogle(): void
    {
        config([
            'services.google.client_id' => 'client-uji',
            'services.google.client_secret' => 'rahasia-uji',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function klaim(array $data = []): SocialiteUser
    {
        return (new SocialiteUser)->setRaw(['email_verified' => true])->map(array_merge([
            'id' => 'sub-google-1',
            'name' => 'Rani Pertiwi',
            'email' => 'rani@google.test',
            'avatar' => null,
        ], $data));
    }

    private function tiruCallback(SocialiteUser $pengguna): void
    {
        $driver = Mockery::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($pengguna);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    public function test_tombol_google_tersembunyi_saat_belum_dikonfigurasi(): void
    {
        $this->get('/login/local')->assertOk()->assertDontSee('Masuk dengan Google');
    }

    public function test_tombol_google_tampil_di_formulir_masuk_saat_dikonfigurasi(): void
    {
        $this->konfigurasiGoogle();

        $this->get('/login/local')->assertOk()->assertSee('Masuk dengan Google');
    }

    public function test_tombol_google_tetap_tampil_saat_wajib_sso_aktif(): void
    {
        $this->konfigurasiGoogle();
        config([
            'services.keycloak.base_url' => 'https://sso.test',
            'services.keycloak.realms' => 'internal',
        ]);
        Setting::set('force_sso', true);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Login dengan SSO')
            ->assertSee('Masuk dengan Google');
    }

    public function test_halaman_lupa_kata_sandi_ikut_menawarkan_google(): void
    {
        $this->konfigurasiGoogle();

        $this->get('/forgot-password')->assertOk()->assertSee('Masuk dengan Google');
    }

    public function test_callback_membuat_akun_google_baru(): void
    {
        $this->konfigurasiGoogle();
        $this->tiruCallback($this->klaim());

        $this->get('/auth/google/callback')->assertRedirect('/user');

        $user = User::query()->where('email', 'rani@google.test')->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google', $user->auth_provider);
        $this->assertSame('rani', $user->username);
        $this->assertSame('sub-google-1', $user->google_sub);
        $this->assertNull($user->password);
        $this->assertSame('user', $user->role);
        $this->assertNotNull($user->last_login_at);
        $this->assertDatabaseHas('login_audit_logs', [
            'user_id' => $user->id,
            'auth_provider' => 'google',
            'status' => 'success',
        ]);
    }

    public function test_username_yang_sudah_terpakai_diberi_akhiran(): void
    {
        $this->konfigurasiGoogle();
        User::factory()->create(['username' => 'rani', 'email' => 'lain@test.local']);
        $this->tiruCallback($this->klaim());

        $this->get('/auth/google/callback')->assertRedirect('/user');

        $this->assertSame('rani-2', User::query()->where('email', 'rani@google.test')->sole()->username);
    }

    public function test_username_cadangan_dihindari(): void
    {
        $this->konfigurasiGoogle();
        $this->tiruCallback($this->klaim(['email' => 'admin@google.test']));

        $this->get('/auth/google/callback')->assertRedirect('/user');

        $this->assertSame('admin-2', User::query()->where('email', 'admin@google.test')->sole()->username);
    }

    public function test_callback_menautkan_akun_lokal_dengan_email_sama(): void
    {
        $this->konfigurasiGoogle();
        $lokal = User::factory()->create([
            'username' => 'ranilokal',
            'email' => 'rani@google.test',
            'password' => 'kata-sandi-lama',
            'auth_provider' => 'local',
            'role' => 'user',
            'is_active' => true,
        ]);
        $this->tiruCallback($this->klaim());

        $this->get('/auth/google/callback')->assertRedirect('/user');

        $this->assertSame(1, User::query()->count());
        $this->assertAuthenticatedAs($lokal->refresh());
        $this->assertSame('local', $lokal->auth_provider);
        $this->assertSame('sub-google-1', $lokal->google_sub);

        // Kata sandi lama tetap berlaku sesudah penautan.
        $this->post('/logout');
        $this->post('/login/local', ['username' => 'rani@google.test', 'password' => 'kata-sandi-lama'])
            ->assertRedirect('/user');
    }

    public function test_callback_menolak_email_milik_penyedia_lain(): void
    {
        $this->konfigurasiGoogle();
        $sso = User::factory()->create([
            'email' => 'rani@google.test',
            'auth_provider' => 'keycloak',
            'password' => null,
            'is_active' => true,
        ]);
        $this->tiruCallback($this->klaim());

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
        $this->assertNull($sso->refresh()->google_sub);
    }

    public function test_callback_menolak_akun_nonaktif(): void
    {
        $this->konfigurasiGoogle();
        User::factory()->create([
            'email' => 'rani@google.test',
            'google_sub' => 'sub-google-1',
            'auth_provider' => 'google',
            'password' => null,
            'is_active' => false,
        ]);
        $this->tiruCallback($this->klaim());

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_callback_menolak_email_yang_belum_terverifikasi(): void
    {
        $this->konfigurasiGoogle();
        $this->tiruCallback($this->klaim()->setRaw(['email_verified' => false]));

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
        $this->assertSame(0, User::query()->count());
    }

    public function test_redirect_google_mengarahkan_ke_google(): void
    {
        $this->konfigurasiGoogle();

        $driver = Mockery::mock(SocialiteProvider::class);
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.test/oauth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $this->get('/auth/google/redirect')->assertRedirect('https://accounts.google.test/oauth');
    }

    public function test_redirect_google_tanpa_kredensial_kembali_ke_halaman_masuk(): void
    {
        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('identifier');
    }

    public function test_akun_hasil_google_tidak_bisa_masuk_lewat_kata_sandi(): void
    {
        $user = User::factory()->create([
            'username' => 'rani',
            'email' => 'rani@google.test',
            'password' => null,
            'auth_provider' => 'google',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->post('/login/local', ['username' => $user->email, 'password' => 'apa-saja'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertFalse(Hash::check('apa-saja', (string) $user->refresh()->password));
    }
}
