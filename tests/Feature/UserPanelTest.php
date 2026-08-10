<?php

namespace Tests\Feature;

use App\Filament\User\Resources\ShortUrlResource\Pages\CreateShortUrl;
use App\Filament\User\Resources\ShortUrlResource\Pages\EditShortUrl;
use App\Models\AuditLog;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserPanelTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    public function test_user_can_create_link_via_panel_and_audit_logged(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(CreateShortUrl::class)
            ->fillForm([
                '_slug' => '',
                'destination_url' => 'https://example.com/create',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('short_urls', [
            'owner_id' => $user->id,
            'destination_url' => 'https://example.com/create',
        ]);

        $link = ShortUrl::query()->where('owner_id', $user->id)->first();
        $this->assertNotNull($link);
        $this->assertNotEmpty($link->slug);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'link.create',
            'resource_type' => 'short_url',
            'resource_id' => $link->id,
        ]);
    }

    public function test_user_can_create_password_protected_link_via_panel(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(CreateShortUrl::class)
            ->fillForm([
                'destination_url' => 'https://example.com/protected',
                'is_active' => true,
                '_protected' => true,
                '_password' => 'rahasia',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $link = ShortUrl::query()->where('owner_id', $user->id)->first();
        $this->assertTrue($link->hasPassword());
        $this->assertNotSame('rahasia', $link->password_hash);
        $this->assertTrue(password_verify('rahasia', $link->password_hash));
    }

    public function test_user_can_edit_own_link_via_panel_and_audit_logged(): void
    {
        $user = $this->user();
        $link = ShortUrl::factory()->create([
            'owner_id' => $user->id,
            'destination_url' => 'https://example.com/old',
        ]);

        Livewire::actingAs($user)
            ->test(EditShortUrl::class, ['record' => $link->id])
            ->fillForm([
                'destination_url' => 'https://example.com/new',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('short_urls', [
            'id' => $link->id,
            'destination_url' => 'https://example.com/new',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'update',
            'resource_type' => 'short_url',
            'resource_id' => $link->id,
        ]);
    }

    public function test_user_cannot_edit_link_of_another_user_via_panel(): void
    {
        $user = $this->user();
        $other = $this->user();
        $link = ShortUrl::factory()->create([
            'owner_id' => $other->id,
        ]);

        $this->actingAs($user)
            ->get($link->owner->id ? '/user/short-urls/'.$link->id.'/edit' : '/user/short-urls')
            ->assertStatus(404);
    }

    public function test_admin_panel_renders_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->assertStringContainsString('App\\Filament\\Widgets\\StatsOverview', $response->getContent());
        $this->assertStringContainsString('App\\Filament\\Widgets\\ClicksPerDayChart', $response->getContent());
    }
}