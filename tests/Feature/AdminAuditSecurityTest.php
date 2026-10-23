<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ShortUrlResource\Pages\CreateShortUrl;
use App\Filament\Admin\Resources\ShortUrlResource\Pages\EditShortUrl;
use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuditSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);
    }

    public function test_admin_link_edit_is_audited(): void
    {
        $admin = $this->admin();
        $link = ShortUrl::factory()->create(['owner_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(EditShortUrl::class, ['record' => $link->id])
            ->fillForm([
                'slug' => $link->slug,
                'destination_url' => 'https://example.com/baru',
                'owner_id' => $admin->id,
                'is_active' => true,
                'starts_at' => null,
                'expires_at' => null,
                'remove_password' => false,
                'new_password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'link.update',
            'resource_type' => 'short_url',
            'resource_id' => $link->id,
        ]);
    }

    public function test_admin_created_link_hashes_password_and_is_audited(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateShortUrl::class)
            ->fillForm([
                'slug' => 'tautan-baru',
                'destination_url' => 'https://example.com',
                'owner_id' => $admin->id,
                'is_active' => true,
                'new_password' => 'rahasia123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $link = ShortUrl::where('slug', 'tautan-baru')->firstOrFail();

        $this->assertTrue(Hash::check('rahasia123', $link->password_hash));
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'link.create',
            'resource_id' => $link->id,
        ]);
    }

    public function test_admin_created_user_is_audited(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Pengguna Baru',
                'username' => 'pengguna-baru',
                'email' => 'baru@example.com',
                'role' => 'user',
                'password' => 'rahasia123',
                'is_active' => 1,
                'auth_provider' => 'local',
                'is_protected' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $baru = User::where('email', 'baru@example.com')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.create',
            'resource_type' => 'user',
            'resource_id' => $baru->id,
        ]);
    }

    public function test_deleting_user_soft_deletes_and_deactivates_links(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);
        $link = ShortUrl::factory()->create(['owner_id' => $target->id]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('delete', $target);

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertFalse($link->refresh()->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.delete',
            'resource_id' => $target->id,
        ]);
    }

    public function test_admin_cannot_delete_self_or_protected_account(): void
    {
        $admin = $this->admin();
        $protected = User::factory()->create([
            'role' => 'admin',
            'auth_provider' => 'local',
            'is_active' => true,
            'is_protected' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableActionHidden('delete', $admin)
            ->assertTableActionHidden('delete', $protected)
            ->callTableBulkAction('delete', [$protected]);

        $this->assertNotSoftDeleted('users', ['id' => $protected->id]);
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'user.delete',
            'resource_id' => $protected->id,
        ]);
    }

    public function test_admin_form_rejects_invalid_slug_and_expired_date(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateShortUrl::class)
            ->fillForm([
                'slug' => 'slug tidak valid!',
                'destination_url' => 'bukan-url',
                'owner_id' => $admin->id,
                'expires_at' => now()->subDay()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug', 'destination_url', 'expires_at']);
    }
}
