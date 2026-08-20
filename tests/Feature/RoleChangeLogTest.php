<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Models\RoleChangeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleChangeLogTest extends TestCase
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

    public function test_role_change_log_created_when_promoting_user_to_admin(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $target->id])
            ->fillForm([
                'name' => $target->name,
                'username' => $target->username,
                'email' => $target->email,
                'role' => 'admin',
                'is_active' => $target->is_active ? 1 : 0,
                'auth_provider' => 'local',
                'is_protected' => $target->is_protected,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('role_change_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'old_role' => 'user',
            'new_role' => 'admin',
        ]);
    }

    public function test_role_change_log_created_when_demoting_admin_to_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'role' => 'admin',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $target->id])
            ->fillForm([
                'name' => $target->name,
                'username' => $target->username,
                'email' => $target->email,
                'role' => 'user',
                'is_active' => $target->is_active ? 1 : 0,
                'auth_provider' => 'local',
                'is_protected' => $target->is_protected,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('role_change_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $target->id,
            'old_role' => 'admin',
            'new_role' => 'user',
        ]);
    }

    public function test_no_log_when_role_unchanged(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $target->id])
            ->fillForm([
                'name' => $target->name,
                'username' => $target->username,
                'email' => $target->email,
                'role' => 'user',
                'is_active' => $target->is_active ? 1 : 0,
                'auth_provider' => 'local',
                'is_protected' => $target->is_protected,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('role_change_logs', [
            'target_user_id' => $target->id,
        ]);
    }
}