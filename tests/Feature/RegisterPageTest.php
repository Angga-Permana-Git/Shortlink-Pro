<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_daftar_menampilkan_contoh_isian_dan_tombol_lihat_sandi(): void
    {
        $halaman = $this->get(route('register'));

        $halaman->assertOk()
            ->assertSee('Mis. Angga_permana')
            ->assertSee('Hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).')
            ->assertSee('Minimal 12 karakter.')
            ->assertSee('data-lihat-sandi="password"', false)
            ->assertSee('data-lihat-sandi="password_confirmation"', false)
            ->assertSee('aria-pressed="false"', false);

        $this->assertSame(2, substr_count($halaman->getContent(), 'data-lihat-sandi='));
    }

    public function test_halaman_daftar_wajib_centang_persetujuan_sebelum_tombol_aktif(): void
    {
        $halaman = $this->get(route('register'));

        $halaman->assertOk()
            ->assertSee('name="setuju"', false)
            ->assertSee('Syarat &amp; Ketentuan', false)
            ->assertSee('Kebijakan Privasi', false)
            ->assertSee('<button class="tombol-auth" type="submit" disabled>', false);
    }

    public function test_daftar_tanpa_centang_persetujuan_ditolak(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Tanpa Setuju',
            'username' => 'tanpa-setuju',
            'email' => 'tanpa@example.test',
            'password' => 'kata-sandi-panjang',
            'password_confirmation' => 'kata-sandi-panjang',
        ])->assertSessionHasErrors('setuju');

        $this->assertDatabaseMissing('users', ['username' => 'tanpa-setuju']);
    }
}
