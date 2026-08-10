<?php

namespace Tests\Feature;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user);

        $this->assertFalse($user->canLogin());
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_user_can_access_user_panel(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get('/user')
            ->assertOk();
    }

    public function test_user_cannot_delete_link(): void
    {
        $user = $this->user();

        $link = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $this->assertFalse($user->can('delete', $link));
        $this->assertTrue($user->can('update', $link));
        $this->assertTrue($user->can('create', ShortUrl::class));
    }

    public function test_user_cannot_manage_others_link(): void
    {
        $user = $this->user();
        $other = $this->user();

        $link = ShortUrl::factory()->create(['owner_id' => $other->id]);

        $this->assertFalse($user->can('update', $link));
        $this->assertFalse($user->can('view', $link));
    }

    public function test_admin_can_delete_any_link(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $link = ShortUrl::factory()->create(['owner_id' => $user->id]);

        $this->assertTrue($admin->can('delete', $link));
        $this->assertTrue($admin->can('update', $link));
    }
}