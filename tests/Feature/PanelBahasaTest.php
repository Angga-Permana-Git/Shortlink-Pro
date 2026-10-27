<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isi panel pengguna harus mengikuti bahasa akun, bukan teks Indonesia yang ditulis tetap.
 */
class PanelBahasaTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_pengguna_berbahasa_inggris_untuk_akun_berbahasa_inggris(): void
    {
        $pengguna = User::factory()->create([
            'is_active' => true,
            'locale' => 'en',
        ]);

        $this->actingAs($pengguna)
            ->get('/user')
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Total Links')
            ->assertSee('Link Shortener')
            ->assertSee('My Links')
            ->assertDontSee('Selamat datang kembali')
            ->assertDontSee('Total Tautan')
            ->assertDontSee('Pemendek Tautan');
    }

    public function test_panel_pengguna_berbahasa_indonesia_untuk_akun_berbahasa_indonesia(): void
    {
        $pengguna = User::factory()->create([
            'is_active' => true,
            'locale' => 'id',
        ]);

        $this->actingAs($pengguna)
            ->get('/user')
            ->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('Total Tautan')
            ->assertSee('Pemendek Tautan')
            ->assertSee('Link Saya')
            ->assertDontSee('Welcome back')
            ->assertDontSee('Total Links');
    }
}
