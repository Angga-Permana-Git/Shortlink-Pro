<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $data
     */
    private function akunLokal(array $data = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Rani Pertiwi',
            'username' => 'rani',
            'email' => 'rani@test.local',
            'password' => 'kata-sandi-lama',
            'auth_provider' => 'local',
            'role' => 'user',
            'is_active' => true,
        ], $data));
    }

    private function tokenDariNotifikasi(User $user): string
    {
        $token = null;

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notifikasi) use (&$token): bool {
            $token = $notifikasi->token;

            return true;
        });

        return (string) $token;
    }

    public function test_halaman_lupa_kata_sandi_tampil(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Lupa kata sandi');
    }

    public function test_tautan_reset_dikirim_dan_emailnya_bisa_dirender(): void
    {
        Notification::fake();
        $user = $this->akunLokal();

        $this->post('/forgot-password', ['email' => 'rani@test.local'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notifikasi) use ($user): bool {
            $mail = $notifikasi->toMail($user);
            $email = $notifikasi->token ? route('password.reset', ['token' => $notifikasi->token, 'email' => $user->email]) : '';

            $this->assertSame('Atur ulang kata sandi '.config('app.name'), $mail->subject);
            $this->assertStringContainsString($email, $mail->render());

            return true;
        });
    }

    public function test_kata_sandi_bisa_diatur_ulang_lewat_tautan(): void
    {
        Notification::fake();
        $user = $this->akunLokal();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $token = $this->tokenDariNotifikasi($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee($user->email);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'kata-sandi-baru-12',
            'password_confirmation' => 'kata-sandi-baru-12',
        ])->assertRedirect('/login/local')->assertSessionHas('status');

        $this->assertTrue(Hash::check('kata-sandi-baru-12', (string) $user->refresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.password_reset',
            'resource_id' => $user->id,
        ]);

        $this->post('/login/local', ['username' => $user->username, 'password' => 'kata-sandi-baru-12'])
            ->assertRedirect('/user');
    }

    public function test_token_palsu_ditolak(): void
    {
        $user = $this->akunLokal();

        $this->post('/reset-password', [
            'token' => 'token-palsu',
            'email' => $user->email,
            'password' => 'kata-sandi-baru-12',
            'password_confirmation' => 'kata-sandi-baru-12',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('kata-sandi-lama', (string) $user->refresh()->password));
    }

    public function test_kata_sandi_baru_wajib_panjang_dan_sama(): void
    {
        $user = $this->akunLokal();

        $this->post('/reset-password', [
            'token' => 'apa-saja',
            'email' => $user->email,
            'password' => 'pendek',
            'password_confirmation' => 'lain',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('kata-sandi-lama', (string) $user->refresh()->password));
    }

    public function test_akun_google_dijelaskan_bukan_akun_lokal(): void
    {
        Notification::fake();
        User::factory()->create([
            'email' => 'rani@google.test',
            'auth_provider' => 'google',
            'password' => null,
            'is_active' => true,
        ]);

        $this->post('/forgot-password', ['email' => 'rani@google.test'])
            ->assertSessionHasErrors([
                'email' => 'Akun ini masuk lewat Google, jadi kata sandinya diatur di akun Google Anda.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_akun_sso_kantor_dijelaskan_bukan_akun_lokal(): void
    {
        Notification::fake();
        User::factory()->create([
            'email' => 'rani@sso.test',
            'auth_provider' => 'keycloak',
            'password' => null,
            'is_active' => true,
        ]);

        $this->post('/forgot-password', ['email' => 'rani@sso.test'])
            ->assertSessionHasErrors([
                'email' => 'Akun ini memakai SSO kantor. Hubungi administrator bila kata sandi Anda bermasalah.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_email_tidak_dikenal_dan_akun_nonaktif_tidak_dibocorkan(): void
    {
        Notification::fake();
        $this->akunLokal(['email' => 'mati@test.local', 'is_active' => false]);

        foreach (['hantu@test.local', 'mati@test.local'] as $email) {
            $this->post('/forgot-password', ['email' => $email])
                ->assertSessionHasErrors(['email' => 'Email tidak dikenali atau akun tidak aktif.']);
        }

        Notification::assertNothingSent();
    }

    public function test_tautan_ditampilkan_di_layar_saat_mailer_log(): void
    {
        Notification::fake();
        config(['mail.default' => 'log']);
        $user = $this->akunLokal();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('tautan_reset', fn (string $tautan) => str_contains($tautan, '/reset-password/'));
    }

    public function test_tautan_tidak_ditampilkan_saat_mailer_bukan_log(): void
    {
        Notification::fake();
        config(['mail.default' => 'smtp']);
        $user = $this->akunLokal();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status')
            ->assertSessionMissing('tautan_reset');
    }

    public function test_pengguna_yang_sudah_masuk_tidak_membuka_halaman_lupa_kata_sandi(): void
    {
        $this->actingAs($this->akunLokal())->get('/forgot-password')->assertRedirect();
    }
}
