<?php

namespace Tests\Feature;

use App\Dto\LinktreeLinkDTO;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoFondasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_punya_canonical_deskripsi_dan_og(): void
    {
        $halaman = $this->get('/');

        $halaman->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="http://localhost"', false)
            ->assertSee('<meta property="og:url" content="http://localhost"', false);
    }

    public function test_halaman_daftar_punya_canonical_deskripsi_dan_og(): void
    {
        $halaman = $this->get(route('register'));

        $halaman->assertOk()
            ->assertSee('<link rel="canonical" href="http://localhost/register"', false)
            ->assertSee('<meta name="description"', false);
    }

    public function test_halaman_masuk_tidak_boleh_diindeks(): void
    {
        $this->get(route('login.local'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<link rel="canonical" href="http://localhost/login/local"', false);
    }

    public function test_halaman_status_tautan_tidak_boleh_diindeks(): void
    {
        $this->get('/slug-tidak-ada-xyz')
            ->assertNotFound()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_redirect_slug_memberi_header_noindex(): void
    {
        $tautan = ShortUrl::factory()->create(['destination_url' => 'https://example.com/a']);

        $this->get('/'.$tautan->slug)
            ->assertRedirect('https://example.com/a')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_qr_memberi_header_noindex(): void
    {
        $tautan = ShortUrl::factory()->create();

        $this->get(route('qr.show', $tautan->slug))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_sitemap_hanya_berisi_halaman_yang_boleh_diindeks(): void
    {
        $pengguna = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $layanan = app(\App\Services\Linktree\LinktreeService::class);
        $profil = $layanan->profile($pengguna);
        $profil->update(['public_slug' => 'toko-contoh', 'is_active' => true]);
        $layanan->syncLinks($profil, [new LinktreeLinkDTO('Toko', 'https://example.com')]);

        $sitemap = $this->get(route('sitemap'));

        $sitemap->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $isi = $sitemap->getContent();
        $this->assertStringContainsString(route('linktree.show', 'toko-contoh'), $isi);
        $this->assertStringContainsString(route('register'), $isi);
        $this->assertStringNotContainsString('/qr/', $isi);
        $this->assertStringNotContainsString('/nonaktif', $isi);
    }

    public function test_robots_menunjuk_ke_sitemap_dan_menutup_panel(): void
    {
        $robots = $this->get('/robots.txt');

        $robots->assertOk();
        $isi = $robots->getContent();
        $this->assertStringContainsString('Sitemap: http://localhost/sitemap.xml', $isi);
        $this->assertStringContainsString('Disallow: /admin', $isi);
        $this->assertStringContainsString('Disallow: /user', $isi);
        $this->assertStringContainsString('Allow: /u/', $isi);
    }
}
