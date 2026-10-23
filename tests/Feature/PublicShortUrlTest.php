<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicShortUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_public_short_url(): void
    {
        Setting::set('force_sso', false);

        $this->post(route('shorten.public'), [
            'destination_url' => 'https://example.com',
            'slug' => 'publik-test',
            'password' => 'rahasia123',
        ])->assertSessionHas('short_url');

        $this->assertDatabaseHas('short_urls', [
            'owner_id' => null,
            'slug' => 'publik-test',
        ]);
    }

    public function test_manual_registration_hashes_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Pengguna Baru',
            'username' => 'pengguna-baru',
            'email' => 'baru@example.test',
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
        ])->assertRedirect('/user');

        $this->assertDatabaseMissing('users', ['password' => 'password-rahasia']);
    }

    public function test_home_page_shows_shortener_options(): void
    {
        Setting::set('force_sso', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kustom')
            ->assertSee('Kata sandi')
            ->assertSee('QR Code')
            ->assertSee('Pendekkan Tautan');
    }

    /**
     * Linktree hanya fasilitas pengguna yang sudah masuk, jadi alur pemendekan
     * di halaman awal tidak boleh menawarkan pengelolaannya (PRDapp.md bagian 5
     * nomor 5).
     */
    public function test_home_page_does_not_offer_linktree(): void
    {
        Setting::set('force_sso', false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('/user/linktree-settings')
            ->assertDontSee('Halaman Linktree Saya');
    }

    public function test_json_shorten_returns_short_url_with_qr(): void
    {
        Setting::set('force_sso', false);

        $response = $this->postJson(route('shorten.public'), [
            'destination_url' => 'https://example.com',
            'slug' => 'publik-json',
        ]);

        $response->assertCreated()->assertJsonPath('data.slug', 'publik-json');

        $this->assertSame(route('qr.show', 'publik-json'), $response->json('data.qr'));
    }

    public function test_qr_endpoint_returns_svg_image(): void
    {
        Setting::set('force_sso', false);

        $this->post(route('shorten.public'), [
            'destination_url' => 'https://example.com',
            'slug' => 'publik-qr',
        ]);

        $this->get(route('qr.show', 'publik-qr'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8')
            ->assertSee('<svg', false);
    }
}
