<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Filament\Admin\Resources\UserResource\Pages\ViewUser;
use App\Models\User;
use App\Services\Admin\AdminUserSupportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserSupportTest extends TestCase
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

    private function pengguna(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'auth_provider' => 'local',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_send_password_reset_link(): void
    {
        $admin = $this->admin();
        $target = $this->pengguna();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('kirim_reset', $target);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.reset_link',
            'resource_id' => $target->id,
        ]);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $target->email,
        ]);
    }

    public function test_admin_can_end_all_target_sessions(): void
    {
        $admin = $this->admin();
        $target = $this->pengguna();
        $tokenLama = $target->remember_token;

        DB::table('sessions')->insert([
            'id' => 'sesi-uji',
            'user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pengujian',
            'payload' => base64_encode(serialize([])),
            'last_activity' => time(),
        ]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('keluarkan_sesi', $target);

        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-uji']);
        $this->assertNotSame($tokenLama, $target->fresh()->remember_token);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.force_logout',
            'resource_id' => $target->id,
        ]);
    }

    public function test_admin_can_start_and_stop_impersonation(): void
    {
        $admin = $this->admin();
        $target = $this->pengguna();

        app(AdminUserSupportService::class)->mulaiImpersonasi($target, $admin);

        $this->assertAuthenticatedAs($target);
        $this->assertSame($admin->id, session('impersonator_id'));

        $response = $this->actingAs($target)
            ->withSession([
                'impersonator_id' => $admin->id,
                'impersonator_name' => $admin->name,
            ])
            ->post('/admin/impersonate/stop');

        $response->assertRedirect('/admin/users');
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.impersonate',
            'resource_id' => $target->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.impersonate_stop',
            'resource_id' => $target->id,
        ]);
    }

    public function test_admin_can_view_user_detail_with_relations(): void
    {
        $admin = $this->admin();
        $target = $this->pengguna();

        Livewire::actingAs($admin)
            ->test(ViewUser::class, ['record' => $target->id])
            ->assertOk()
            ->assertSee($target->name)
            ->assertSee('Tautan Pengguna')
            ->assertSee('Riwayat Masuk')
            ->assertSee('Jejak Audit');
    }

    public function test_admin_can_edit_own_profile_but_not_own_role(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $admin->id])
            ->fillForm([
                'name' => 'Admin Diperbarui',
                'role' => 'user',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $admin->refresh();

        $this->assertSame('Admin Diperbarui', $admin->name);
        $this->assertSame('admin', $admin->role);
    }
}
