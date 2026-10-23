<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\LinktreeProfileResource\Pages\EditLinktreeProfile;
use App\Filament\Admin\Resources\LinktreeProfileResource\Pages\ListLinktreeProfiles;
use App\Models\AuditLog;
use App\Models\LinktreeProfile;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminModerationTest extends TestCase
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

    private function profil(User $pemilik, string $slug = 'profil-saya'): LinktreeProfile
    {
        return LinktreeProfile::create([
            'user_id' => $pemilik->id,
            'public_slug' => $slug,
            'display_name' => 'Profil '.$pemilik->name,
            'bio' => 'Keterangan awal',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_moderate_linktree_profile(): void
    {
        $admin = $this->admin();
        $pemilik = User::factory()->create();
        $profil = $this->profil($pemilik);

        Livewire::actingAs($admin)
            ->test(ListLinktreeProfiles::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$profil]);

        Livewire::actingAs($admin)
            ->test(EditLinktreeProfile::class, ['record' => $profil->id])
            ->fillForm([
                'display_name' => 'Nama Baru',
                'bio' => 'Keterangan baru',
                'public_slug' => 'alamat-baru',
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('linktree_profiles', [
            'id' => $profil->id,
            'display_name' => 'Nama Baru',
            'public_slug' => 'alamat-baru',
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_use_another_profile_public_slug(): void
    {
        $admin = $this->admin();
        $pertama = $this->profil(User::factory()->create(), 'sudah-dipakai');
        $kedua = $this->profil(User::factory()->create(), 'milik-kedua');

        Livewire::actingAs($admin)
            ->test(EditLinktreeProfile::class, ['record' => $kedua->id])
            ->fillForm([
                'display_name' => $kedua->display_name,
                'bio' => $kedua->bio,
                'public_slug' => $pertama->public_slug,
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['public_slug']);
    }

    public function test_admin_can_export_audit_log_as_csv(): void
    {
        $admin = $this->admin();
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_type' => User::class,
            'action' => 'user.update',
            'resource_type' => 'user',
            'resource_id' => $admin->id,
            'metadata' => ['catatan' => 'uji ekspor'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.export', 'audit'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSee('user.update')
            ->assertSee('uji ekspor');
    }

    public function test_non_admin_cannot_export_logs(): void
    {
        $pengguna = User::factory()->create(['role' => 'user']);

        $this->actingAs($pengguna)
            ->get(route('admin.export', 'audit'))
            ->assertForbidden();
    }

    public function test_purge_command_removes_only_old_logs(): void
    {
        $admin = $this->admin();
        $link = ShortUrl::factory()->create(['owner_id' => $admin->id]);

        DB::table('audit_logs')->insert([
            ['actor_id' => $admin->id, 'action' => 'log.lama', 'created_at' => now()->subDays(100)],
            ['actor_id' => $admin->id, 'action' => 'log.baru', 'created_at' => now()],
        ]);
        DB::table('login_audit_logs')->insert([
            ['user_id' => $admin->id, 'status' => 'success', 'created_at' => now()->subDays(100)],
            ['user_id' => $admin->id, 'status' => 'success', 'created_at' => now()],
        ]);
        DB::table('click_events')->insert([
            ['short_url_id' => $link->id, 'status' => 'success', 'created_at' => now()->subDays(100)],
            ['short_url_id' => $link->id, 'status' => 'success', 'created_at' => now()],
        ]);
        DB::table('role_change_logs')->insert([
            ['target_user_id' => $admin->id, 'new_role' => 'user', 'created_at' => now()->subDays(100)],
            ['target_user_id' => $admin->id, 'new_role' => 'user', 'created_at' => now()],
        ]);

        $this->artisan('logs:purge', ['--days' => 90])->assertSuccessful();

        $this->assertDatabaseMissing('audit_logs', ['action' => 'log.lama']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'log.baru']);
        $this->assertSame(1, DB::table('login_audit_logs')->count());
        $this->assertSame(1, DB::table('click_events')->count());
        $this->assertSame(1, DB::table('role_change_logs')->count());
    }
}
