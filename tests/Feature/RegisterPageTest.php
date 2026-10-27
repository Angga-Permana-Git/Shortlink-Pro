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
            ->assertSee('mis. budi_santoso')
            ->assertSee('Huruf, angka, tanda hubung, dan garis bawah.')
            ->assertSee('Minimal 12 karakter.')
            ->assertSee('data-lihat-sandi="password"', false)
            ->assertSee('data-lihat-sandi="password_confirmation"', false)
            ->assertSee('aria-pressed="false"', false);

        $this->assertSame(2, substr_count($halaman->getContent(), 'data-lihat-sandi='));
    }
}
