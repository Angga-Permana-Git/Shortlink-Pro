<?php

namespace Tests\Unit;

use App\Dto\LinktreeLinkDTO;
use App\Dto\UpdateLinktreeProfileDTO;
use App\Models\User;
use App\Services\Linktree\LinktreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinktreeServiceTest extends TestCase
{
    use RefreshDatabase;

    private LinktreeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LinktreeService::class);
    }

    public function test_sync_links_stores_items_in_given_order(): void
    {
        $profile = $this->service->profile($this->user());

        $this->service->syncLinks($profile, [
            new LinktreeLinkDTO('Pertama', 'https://example.com/1'),
            new LinktreeLinkDTO('Kedua', 'https://example.com/2'),
        ]);

        $this->assertSame(['Pertama', 'Kedua'], $profile->links()->pluck('label')->all());
        $this->assertSame([0, 1], $profile->links()->pluck('sort_order')->all());
    }

    public function test_sync_links_replaces_previous_items(): void
    {
        $profile = $this->service->profile($this->user());

        $this->service->syncLinks($profile, [
            new LinktreeLinkDTO('Lama', 'https://example.com/lama'),
        ]);

        $this->service->syncLinks($profile, [
            new LinktreeLinkDTO('Baru', 'https://example.com/baru'),
        ]);

        $this->assertSame(['Baru'], $profile->links()->pluck('label')->all());
    }

    public function test_update_only_touches_the_callers_profile(): void
    {
        $user = $this->user();
        $otherProfile = $this->service->profile(User::factory()->create(['username' => 'orang-lain']));

        $profile = $this->service->update($user, new UpdateLinktreeProfileDTO(displayName: 'Nama Baru'));

        $this->assertSame($user->id, $profile->user_id);
        $this->assertSame('Nama Baru', $profile->display_name);
        $this->assertNotSame('Nama Baru', $otherProfile->refresh()->display_name);
    }

    public function test_update_can_clear_bio(): void
    {
        $user = $this->user();

        $this->service->update($user, new UpdateLinktreeProfileDTO(bio: 'Sebelumnya ada.', bioIsSet: true));

        $profile = $this->service->update($user, new UpdateLinktreeProfileDTO(bio: null, bioIsSet: true));

        $this->assertNull($profile->bio);
    }

    public function test_public_profile_only_returns_active_links(): void
    {
        $profile = $this->service->profile($this->user(['username' => 'pemilik']));
        $profile->update(['public_slug' => 'pemilik']);

        $this->service->syncLinks($profile, [
            new LinktreeLinkDTO('Aktif', 'https://example.com/aktif'),
            new LinktreeLinkDTO('Nonaktif', 'https://example.com/nonaktif', false),
        ]);

        $this->assertSame(['Aktif'], $this->service->publicProfile('pemilik')->links->pluck('label')->all());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'pengguna',
            'role' => 'user',
            'is_active' => true,
        ], $attributes));
    }
}
