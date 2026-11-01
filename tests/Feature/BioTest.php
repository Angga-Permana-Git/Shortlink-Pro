<?php

namespace Tests\Feature;

use App\Dto\BioLinkDTO;
use App\Dto\UpdateBioProfileDTO;
use App\Filament\User\Pages\BioSettings;
use App\Models\User;
use App\Services\Bio\BioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BioTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        $user = User::factory()->create([
            'username' => 'penulis',
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);

        app(BioService::class)->profile($user)->update(['public_slug' => 'penulis']);

        return $user;
    }

    public function test_public_page_shows_active_links_only(): void
    {
        $service = app(BioService::class);
        $profile = $service->profile($this->user());

        $service->syncLinks($profile, [
            new BioLinkDTO('Tautan Tampil', 'https://example.com'),
            new BioLinkDTO('Tautan Tersembunyi', 'https://example.com/hidden', false),
        ]);

        $this->get(route('bio.show', 'penulis'))
            ->assertOk()
            ->assertSee('Tautan Tampil')
            ->assertDontSee('Tautan Tersembunyi');
    }

    public function test_public_page_not_found_for_unknown_address(): void
    {
        $this->get(route('bio.show', 'belum-terdaftar'))->assertNotFound();
    }

    /**
     * Alamat publik dipilih pemiliknya sendiri, jadi username login tidak lagi
     * bisa dipakai sebagai alamat halaman.
     */
    public function test_public_page_uses_custom_address_instead_of_username(): void
    {
        $user = $this->user();

        app(BioService::class)->profile($user)->update(['public_slug' => 'tautan-penulis']);

        $this->get(route('bio.show', 'tautan-penulis'))->assertOk();
        $this->get(route('bio.show', 'penulis'))->assertNotFound();
    }

    public function test_public_page_not_found_while_address_is_empty(): void
    {
        $user = User::factory()->create(['username' => 'tanpa-alamat', 'role' => 'user', 'is_active' => true]);

        app(BioService::class)->profile($user);

        $this->get(route('bio.show', 'tanpa-alamat'))->assertNotFound();
    }

    public function test_public_page_shows_uploaded_profile_logo(): void
    {
        Storage::fake('public');

        $user = $this->user();
        $profil = app(BioService::class)->profile($user);
        $profil->avatar_path = 'bio/'.$user->id.'/logo.png';
        $profil->save();

        $this->get(route('bio.show', 'penulis'))
            ->assertOk()
            ->assertSee('profil__lambang-gambar');
    }

    public function test_public_page_falls_back_to_initial_without_logo(): void
    {
        app(BioService::class)->profile($this->user());

        $this->get(route('bio.show', 'penulis'))
            ->assertOk()
            ->assertDontSee('profil__lambang-gambar');
    }

    public function test_unpublished_profile_is_not_shown(): void
    {
        app(BioService::class)->update($this->user(), new UpdateBioProfileDTO(
            displayName: 'Penulis Demo',
            isActive: false,
        ));

        $this->get(route('bio.show', 'penulis'))->assertNotFound();
    }

    public function test_user_panel_has_bio_page(): void
    {
        $this->actingAs($this->user())
            ->get('/user/bio-settings')
            ->assertOk()
            ->assertSee('Halaman Link in Bio Saya');
    }

    /**
     * Halaman Link in Bio publik boleh dibuka tamu, tetapi pengaturannya hanya
     * untuk pengguna yang sudah masuk (PRDapp.md bagian 5 nomor 5).
     */
    public function test_guest_cannot_manage_bio(): void
    {
        $this->get('/user/bio-settings')->assertRedirect();
    }

    public function test_user_can_save_profile_and_links_from_panel(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(BioSettings::class)
            ->fillForm([
                'display_name' => 'Penulis Demo',
                'bio' => 'Berbagi tautan pilihan.',
                'public_slug' => 'penulis-demo',
                'is_active' => true,
                'links' => [
                    ['label' => 'Situs Utama', 'url' => 'https://example.com', 'is_active' => true],
                    ['label' => 'Blog', 'url' => 'https://example.com/blog', 'is_active' => true],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bio_profiles', [
            'user_id' => $user->id,
            'display_name' => 'Penulis Demo',
            'public_slug' => 'penulis-demo',
        ]);

        $this->assertSame(
            ['Situs Utama', 'Blog'],
            app(BioService::class)->profile($user)->links()->pluck('label')->all(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'bio.update',
        ]);
    }
}
