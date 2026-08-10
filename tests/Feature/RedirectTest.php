<?php

namespace Tests\Feature;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_link_redirects_to_destination(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->create([
            'owner_id' => $owner->id,
            'slug' => 'testactive',
            'destination_url' => 'https://laravel.com',
        ]);

        $response = $this->get(route('redirect.show', 'testactive'));

        $response->assertStatus(302);
        $response->assertRedirect('https://laravel.com');

        $this->assertDatabaseHas('click_events', [
            'short_url_id' => $link->id,
            'status' => 'success',
        ]);

        $this->assertSame(1, $link->fresh()->total_clicks);
    }

    public function test_inactive_link_shows_error(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->inactive()->create([
            'owner_id' => $owner->id,
            'slug' => 'testinactive',
        ]);

        $this->get(route('redirect.show', 'testinactive'))->assertOk();

        $this->assertDatabaseHas('click_events', [
            'short_url_id' => $link->id,
            'status' => 'inactive',
        ]);
    }

    public function test_expired_link_shows_error(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->expired()->create([
            'owner_id' => $owner->id,
            'slug' => 'testexpired',
        ]);

        $this->get(route('redirect.show', 'testexpired'))->assertOk();

        $this->assertDatabaseHas('click_events', [
            'short_url_id' => $link->id,
            'status' => 'expired',
        ]);
    }

    public function test_unknown_slug_shows_not_found(): void
    {
        $this->get('/does-not-exist')->assertOk();
    }

    public function test_password_protected_link_prompts_for_password(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->protected()->create([
            'owner_id' => $owner->id,
            'slug' => 'testpw',
        ]);

        $this->get(route('redirect.show', 'testpw'))
            ->assertOk()
            ->assertSee('Link Dilindungi');
    }

    public function test_correct_password_unlocks_redirect(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->protected()->create([
            'owner_id' => $owner->id,
            'slug' => 'testpwok',
            'destination_url' => 'https://laravel.com',
        ]);

        $this->post(route('redirect.unlock', 'testpwok'), ['password' => 'secret'])
            ->assertRedirect('https://laravel.com');

        $this->assertSame(1, $link->fresh()->total_clicks);
    }

    public function test_wrong_password_fails(): void
    {
        $owner = User::factory()->create();

        $link = ShortUrl::factory()->protected()->create([
            'owner_id' => $owner->id,
            'slug' => 'testpwno',
        ]);

        $this->post(route('redirect.unlock', 'testpwno'), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('click_events', [
            'short_url_id' => $link->id,
            'status' => 'wrong_password',
        ]);
    }
}