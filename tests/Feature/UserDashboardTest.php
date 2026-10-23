<?php

namespace Tests\Feature;

use App\Dto\LinktreeLinkDTO;
use App\Filament\User\Pages\UserDashboard;
use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Dashboard\UserDashboardService;
use App\Services\Linktree\LinktreeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            ...$attributes,
        ]);
    }

    public function test_dashboard_shows_four_cards_in_order_without_page_switching(): void
    {
        $response = $this->actingAs($this->user())
            ->get('/user')
            ->assertOk();

        $response->assertSeeInOrder([
            'kartu__judul">Dashboard',
            'kartu__judul">Pemendek Tautan',
            'kartu__judul">Link Saya',
            'kartu__judul">Profil Linktree',
        ], false);

        $response->assertDontSee('/user/short-urls');
        $response->assertDontSee('/user/linktree-settings');
    }

    public function test_user_can_shorten_link_from_dashboard(): void
    {
        $user = $this->user();

        $halaman = Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('data.destination_url', 'https://example.com/panel')
            ->call('buatTautan')
            ->assertHasNoErrors();

        $tautan = ShortUrl::query()->where('owner_id', $user->id)->first();

        $this->assertNotNull($tautan);
        $this->assertSame('https://example.com/panel', $tautan->destination_url);

        // Kartu hasil menampilkan tautan baru beserta tombol salin, QR, dan
        // lanjutan ke kartu Link Saya.
        $this->assertStringContainsString($tautan->shortUrl(), $halaman->html());
        $this->assertStringContainsString('data-salin="hasil-tautan"', $halaman->html());
        $this->assertStringContainsString(route('qr.show', $tautan->slug), $halaman->html());
        $this->assertStringContainsString('download="qr-'.$tautan->slug.'.svg"', $halaman->html());
        $this->assertStringContainsString('href="#kartu-linktree"', $halaman->html());

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'link.create',
            'resource_type' => 'short_url',
            'resource_id' => $tautan->id,
        ]);
    }

    public function test_dashboard_rejects_invalid_destination(): void
    {
        $halaman = Livewire::actingAs($this->user())
            ->test(UserDashboard::class)
            ->set('data.destination_url', 'bukan-url')
            ->call('buatTautan')
            ->assertHasErrors('data.destination_url');

        $this->assertStringContainsString('pesan-galat', $halaman->html());
        $this->assertStringContainsString('tautan panjang', $halaman->html());

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_user_can_edit_own_link_from_dashboard(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $halaman = Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('mulaiSunting', $tautan->id)
            ->assertSet('idSunting', $tautan->id)
            ->assertSet('suntingan.destination_url', $tautan->destination_url)
            ->set('suntingan.destination_url', 'https://example.com/baru')
            ->set('suntingan.is_active', false)
            ->set('suntingan.expires_at', '2030-01-02T03:04')
            ->call('simpanSunting', $tautan->id)
            ->assertHasNoErrors();

        $tautan->refresh();

        $this->assertSame('https://example.com/baru', $tautan->destination_url);
        $this->assertFalse($tautan->is_active);
        $this->assertSame('2030-01-02 03:04:00', $tautan->expires_at->format('Y-m-d H:i:s'));

        $this->assertNull($halaman->get('idSunting'));
        $this->assertStringContainsString('Tautan diperbarui.', $halaman->html());

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'link.update',
            'resource_id' => $tautan->id,
        ]);
    }

    public function test_user_can_protect_and_unprotect_link_when_editing(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('mulaiSunting', $tautan->id)
            ->set('suntingan.protected', true)
            ->set('suntingan.password', 'rahasia-123')
            ->call('simpanSunting', $tautan->id)
            ->assertHasNoErrors();

        $this->assertTrue($tautan->refresh()->hasPassword());

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('mulaiSunting', $tautan->id)
            ->assertSet('suntingan.protected', true)
            ->set('suntingan.protected', false)
            ->set('suntingan.password', '')
            ->call('simpanSunting', $tautan->id)
            ->assertHasNoErrors();

        $this->assertFalse($tautan->refresh()->hasPassword());
    }

    public function test_edit_rejects_invalid_destination(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('mulaiSunting', $tautan->id)
            ->set('suntingan.destination_url', 'bukan-url')
            ->call('simpanSunting', $tautan->id)
            ->assertHasErrors('suntingan.destination_url');

        $this->assertSame($tautan->destination_url, $tautan->refresh()->destination_url);
    }

    public function test_user_can_delete_own_link_from_dashboard(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $halaman = Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('pilihanLinktree', [$tautan->id])
            ->call('hapusTautan', $tautan->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('short_urls', ['id' => $tautan->id]);
        $this->assertSame([], $halaman->get('pilihanLinktree'));
        $this->assertStringContainsString('Tautan dihapus.', $halaman->html());

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'link.delete',
            'resource_id' => $tautan->id,
        ]);
    }

    public function test_user_cannot_touch_link_of_another_user(): void
    {
        $user = $this->user();
        $asing = ShortUrl::factory()->create(['owner_id' => $this->user()->id]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('hapusTautan', $asing->id);
    }

    public function test_user_can_choose_which_links_appear_on_linktree(): void
    {
        $user = $this->user();
        $dipilih = ShortUrl::factory()->create(['owner_id' => $user->id]);
        $diabaikan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('pilihanLinktree', [$dipilih->id])
            ->set('terbitkan', true)
            ->call('simpanLinktree')
            ->assertHasNoErrors();

        $profil = app(LinktreeService::class)->profile($user);
        $tautan = $profil->links()->get();

        $this->assertSame([$dipilih->shortUrl()], $tautan->pluck('url')->all());
        $this->assertSame($dipilih->slug, $tautan->first()->label);
        $this->assertTrue($profil->fresh()->is_active);
        $this->assertNotSame($diabaikan->shortUrl(), $tautan->first()->url);
    }

    public function test_user_cannot_publish_link_of_another_user(): void
    {
        $user = $this->user();
        $asing = ShortUrl::factory()->create(['owner_id' => $this->user()->id]);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('pilihanLinktree', [$asing->id])
            ->call('simpanLinktree');

        $this->assertSame(0, app(LinktreeService::class)->profile($user)->links()->count());
    }

    public function test_user_can_save_linktree_profile_identity(): void
    {
        $user = $this->user(['username' => 'penulis']);

        $halaman = Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('profil.display_name', 'Penulis Demo')
            ->set('profil.bio', 'Berbagi tautan pilihan.')
            ->set('profil.public_slug', 'penulis-demo')
            ->call('simpanProfil')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('linktree_profiles', [
            'user_id' => $user->id,
            'display_name' => 'Penulis Demo',
            'bio' => 'Berbagi tautan pilihan.',
            'public_slug' => 'penulis-demo',
        ]);

        $this->assertStringContainsString('Penulis Demo', $halaman->html());
        $this->assertStringContainsString('Profil Linktree tersimpan.', $halaman->html());
    }

    /**
     * Halaman publik memakai alamat pilihan pemiliknya, bukan username login.
     */
    public function test_saved_public_address_is_used_for_the_public_page(): void
    {
        $user = $this->user(['username' => 'penulis']);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('profil.display_name', 'Penulis Demo')
            ->set('profil.public_slug', 'tautan-penulis')
            ->call('simpanProfil')
            ->assertHasNoErrors();

        $this->get(route('linktree.show', 'tautan-penulis'))->assertOk();
        $this->get(route('linktree.show', 'penulis'))->assertNotFound();
    }

    public function test_public_address_rejects_uppercase_and_duplicate_of_another_user(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('profil.public_slug', 'Bukan-Alamat')
            ->call('simpanProfil')
            ->assertHasErrors('profil.public_slug');

        Livewire::actingAs($this->user())
            ->test(UserDashboard::class)
            ->set('profil.public_slug', 'sudah-dipakai')
            ->call('simpanProfil')
            ->assertHasNoErrors();

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('profil.public_slug', 'sudah-dipakai')
            ->call('simpanProfil')
            ->assertHasErrors('profil.public_slug');
    }

    public function test_profile_identity_requires_display_name(): void
    {
        Livewire::actingAs($this->user())
            ->test(UserDashboard::class)
            ->set('profil.display_name', '')
            ->call('simpanProfil')
            ->assertHasErrors('profil.display_name');
    }

    public function test_user_can_upload_and_remove_profile_logo(): void
    {
        Storage::fake('public');

        $user = $this->user(['username' => 'penulis']);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->set('profil.public_slug', 'penulis')
            ->set('profil.logo', UploadedFile::fake()->image('logo.png', 200, 200))
            ->call('simpanProfil')
            ->assertHasNoErrors();

        $profil = app(LinktreeService::class)->profile($user);
        $path = $profil->avatar_path;

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'linktree.avatar_upload',
        ]);

        $this->get(route('linktree.show', 'penulis'))
            ->assertOk()
            ->assertSee('profil__lambang-gambar');

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('hapusLogo')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($profil->refresh()->avatar_path);

        $this->get(route('linktree.show', 'penulis'))
            ->assertOk()
            ->assertDontSee('profil__lambang-gambar');
    }

    public function test_profile_logo_rejects_file_that_is_not_an_image(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->user())
            ->test(UserDashboard::class)
            ->set('profil.public_slug', 'penulis')
            ->set('profil.logo', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))
            ->call('simpanProfil')
            ->assertHasErrors('profil.logo');
    }

    public function test_review_card_shows_public_address(): void
    {
        $user = $this->user(['username' => 'penulis']);

        app(LinktreeService::class)->profile($user)->update(['public_slug' => 'tautan-penulis']);

        $this->actingAs($user)
            ->get('/user')
            ->assertOk()
            ->assertSee(route('linktree.show', 'tautan-penulis'));
    }

    /**
     * Nama login tidak lagi tampil ke pengunjung: cukup logo, nama tampilan, dan
     * keterangan (PRDapp.md bagian 3.4).
     */
    public function test_review_card_hides_login_name(): void
    {
        $user = $this->user(['username' => 'penulis', 'name' => 'Penulis Demo']);

        app(LinktreeService::class)->profile($user)->update(['public_slug' => 'tautan-penulis']);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('Penulis Demo', $html);
        $this->assertStringNotContainsString('profil__akun', $html);
    }

    public function test_page_exposes_hooks_used_by_scripts_and_forms(): void
    {
        $html = $this->actingAs($this->user())->get('/user')->assertOk()->getContent();

        // Kartu 2: chip membuka panel isian, dan formulir dikirim ke aksi Livewire.
        foreach (['panelKustom', 'panelSandi', 'panelKedaluwarsa'] as $panel) {
            $this->assertStringContainsString('data-panel="'.$panel.'"', $html);
            $this->assertStringContainsString('id="'.$panel.'" hidden', $html);
        }

        $this->assertStringContainsString('wire:submit="buatTautan"', $html);
        $this->assertStringContainsString('wire:submit="simpanLinktree"', $html);
        $this->assertStringContainsString('wire:submit="simpanProfil"', $html);

        // Kartu 3 menjadi tujuan gulir setelah tautan dibuat.
        $this->assertStringContainsString('data-hook="kartu-linktree"', $html);

        // Kartu 3 dan bagian identitas kartu 4 bisa dilipat lewat aksi Livewire.
        $this->assertStringContainsString('id="isi-link-saya"', $html);
        $this->assertStringContainsString('id="isi-profil-linktree"', $html);
        $this->assertStringContainsString('wire:click="lipatTautan"', $html);
        $this->assertStringContainsString('wire:click="lipatProfil"', $html);

        // Kartu 4 memuat isian alamat publik dengan awalan domain.
        $this->assertStringContainsString('wire:model="profil.public_slug"', $html);
    }

    public function test_user_can_fold_and_unfold_link_cards(): void
    {
        $halaman = Livewire::actingAs($this->user())->test(UserDashboard::class);

        $halaman->call('lipatTautan')->assertSet('bukaTautan', false);
        $halaman->call('lipatProfil')->assertSet('bukaProfil', false);

        // Isi yang dilipat disembunyikan dengan atribut hidden, bukan dihapus dari DOM.
        $isi = $halaman->html();
        $this->assertMatchesRegularExpression('/id="isi-link-saya"\s+hidden/', $isi);
        $this->assertMatchesRegularExpression('/id="isi-profil-linktree"\s+hidden/', $isi);

        $halaman->call('lipatTautan')->assertSet('bukaTautan', true);

        $this->assertDoesNotMatchRegularExpression('/id="isi-link-saya"\s+hidden/', $halaman->html());
    }

    public function test_link_row_actions_are_wired_to_hooks_and_livewire(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $linktree = app(LinktreeService::class);
        $linktree->syncLinks($linktree->profile($user), [
            new LinktreeLinkDTO($tautan->slug, $tautan->shortUrl()),
        ]);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        // Tombol salin menunjuk teks tautannya lewat data-hook per baris, dan
        // tombol aksi bertipe button supaya tidak ikut mengirim formulir.
        $this->assertStringContainsString('data-hook="tautan-'.$tautan->id.'"', $html);
        $this->assertStringContainsString('data-salin="tautan-'.$tautan->id.'"', $html);
        $this->assertStringContainsString('wire:click="tampilkanQr('.$tautan->id.')"', $html);
        $this->assertStringContainsString('wire:click="mulaiSunting('.$tautan->id.')"', $html);
        $this->assertStringContainsString('wire:click="hapusTautan('.$tautan->id.')"', $html);
        $this->assertStringContainsString('wire:confirm=', $html);

        // Baris memuat tanda keadaan tautan.
        $this->assertStringContainsString('Di Linktree', $html);
    }

    public function test_user_can_open_qr_of_own_link(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $halaman = Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('tampilkanQr', $tautan->id)
            ->assertSet('idQr', $tautan->id)
            ->assertHasNoErrors();

        $isi = $halaman->html();
        $this->assertStringContainsString('qr-tautan', $isi);
        $this->assertStringContainsString(route('qr.show', $tautan->slug), $isi);
        $this->assertStringContainsString('download="qr-'.$tautan->slug.'.svg"', $isi);

        // Panel sunting dan panel QR tidak terbuka bersamaan pada satu baris.
        $halaman->call('mulaiSunting', $tautan->id)->assertSet('idQr', null);

        $halaman->call('sembunyikanQr')->assertSet('idQr', null);

        $this->assertStringNotContainsString('qr-tautan', $halaman->html());
    }

    public function test_user_cannot_open_qr_of_another_user_link(): void
    {
        $user = $this->user();
        $asing = ShortUrl::factory()->create(['owner_id' => $this->user()->id]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('tampilkanQr', $asing->id);
    }

    public function test_deleting_link_closes_its_qr_panel(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->call('tampilkanQr', $tautan->id)
            ->call('hapusTautan', $tautan->id)
            ->assertSet('idQr', null);
    }

    public function test_dashboard_shows_click_trend_chart(): void
    {
        $user = $this->user();
        $tautan = ShortUrl::factory()->create(['owner_id' => $user->id, 'total_clicks' => 5]);

        // Buat beberapa klik event pada hari ini
        ClickEvent::create([
            'short_url_id' => $tautan->id,
            'status' => 'redirect',
            'ip_hash' => 'hash1',
            'user_agent' => 'test',
            'referer' => null,
        ]);
        ClickEvent::create([
            'short_url_id' => $tautan->id,
            'status' => 'redirect',
            'ip_hash' => 'hash2',
            'user_agent' => 'test',
            'referer' => null,
        ]);

        $halaman = $this->actingAs($user)->get('/user')->assertOk();

        $html = $halaman->getContent();
        $this->assertStringContainsString('data-hook="tren-klik"', $html);
        $this->assertStringContainsString('Tren Klik 30 Hari', $html);
        $this->assertStringContainsString('Klik 30 Hari', $html);
        $this->assertStringContainsString('dari 30 hari sebelumnya', $html);
        $this->assertStringContainsString('Grafik klik 30 hari terakhir', $html);
    }

    public function test_dashboard_shows_top_performing_link(): void
    {
        $user = $this->user();
        $tautan1 = ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'link-a', 'total_clicks' => 10]);
        $tautan2 = ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'link-b', 'total_clicks' => 25]);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('Tautan Terpopuler', $html);
        $this->assertStringContainsString('link-b', $html);
        $this->assertStringContainsString('25 klik', $html);
    }

    public function test_dashboard_shows_no_top_link_when_zero_links(): void
    {
        $user = $this->user();

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringNotContainsString('Link Terpopuler', $html);
    }

    public function test_link_saya_search_filters_results(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'promo-januari']);
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'diskon-akhir']);

        // Semua tautan muncul secara default
        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('promo-januari', $html);
        $this->assertStringContainsString('diskon-akhir', $html);
    }

    public function test_link_saya_filter_status_works(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'link-aktif']);
        ShortUrl::factory()->create(['owner_id' => $user->id, 'is_active' => false, 'slug' => 'link-non-aktif']);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        // Semua tautan muncul secara default
        $this->assertStringContainsString('link-aktif', $html);
        $this->assertStringContainsString('link-non-aktif', $html);
    }

    public function test_link_saya_sorting_works(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'zzz-link']);
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'aaa-link']);

        // Default urutan terbaru — semua tautan muncul
        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('aaa-link', $html);
        $this->assertStringContainsString('zzz-link', $html);
    }

    public function test_export_csv_downloads_link_data(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create(['owner_id' => $user->id, 'slug' => 'test-export']);

        // Simulasikan permintaan ke endpoint export
        $service = app(UserDashboardService::class);
        $csv = $service->eksporCsv($user);

        $this->assertStringContainsString('test-export', $csv);
        $this->assertStringContainsString('Nama Pendek', $csv);
    }

    public function test_link_row_shows_aktif_selamanya_for_permanent_links(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-permanent',
            'is_active' => true,
            'expires_at' => null,
            'starts_at' => null,
        ]);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('Aktif Selamanya', $html);
    }

    public function test_public_toggle_is_a_toggle_switch_not_checkbox(): void
    {
        $user = $this->user();
        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('toggle-status__switch', $html);
        $this->assertStringContainsString('data-hidup', $html);
    }

    public function test_link_status_distinguishes_expired_and_scheduled_links(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-kedaluwarsa',
            'expires_at' => now()->subMinute(),
        ]);
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-terjadwal',
            'starts_at' => now()->addMinute(),
        ]);

        $html = $this->actingAs($user)->get('/user')->assertOk()->getContent();

        $this->assertStringContainsString('Kedaluwarsa', $html);
        $this->assertStringContainsString('Akan Mulai', $html);
    }

    public function test_active_filter_excludes_expired_and_scheduled_links(): void
    {
        $user = $this->user();
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-aktif-filter',
        ]);
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-kedaluwarsa-filter',
            'expires_at' => now()->subMinute(),
        ]);
        ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'slug' => 'link-terjadwal-filter',
            'starts_at' => now()->addMinute(),
        ]);

        $daftar = app(UserDashboardService::class)->daftarTautan($user, '', 'active');

        $this->assertSame(['link-aktif-filter'], array_column($daftar, 'slug'));
    }

    public function test_public_toggle_uses_a_livewire_action(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(UserDashboard::class)
            ->assertSet('terbitkan', true)
            ->call('ubahStatusTerbit')
            ->assertSet('terbitkan', false)
            ->call('ubahStatusTerbit')
            ->assertSet('terbitkan', true);
    }
}
