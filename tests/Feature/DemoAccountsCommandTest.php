<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_demo_admin_and_user(): void
    {
        $this->artisan('app:create-demo-accounts', ['--password' => 'kata-sandi-demo'])
            ->assertSuccessful();

        $admin = User::query()->where('username', 'admin')->first();
        $user = User::query()->where('username', 'user')->first();

        $this->assertNotNull($admin);
        $this->assertNotNull($user);
        $this->assertSame(Role::Admin->value, $admin->role);
        $this->assertSame(Role::User->value, $user->role);
        $this->assertSame('local', $admin->auth_provider);
        $this->assertTrue($admin->is_active);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('kata-sandi-demo', $admin->password));
        $this->assertTrue(Hash::check('kata-sandi-demo', $user->password));
    }

    public function test_command_updates_existing_account_without_duplicating(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@company.local',
            'password' => 'password-lama',
            'role' => Role::User->value,
        ]);

        $this->artisan('app:create-demo-accounts', ['--password' => 'kata-sandi-baru'])
            ->assertSuccessful();

        $this->assertSame(1, User::query()->where('username', 'admin')->count());

        $admin = User::query()->where('username', 'admin')->first();

        $this->assertSame(Role::Admin->value, $admin->role);
        $this->assertTrue(Hash::check('kata-sandi-baru', $admin->password));
    }

    public function test_command_rejects_short_password(): void
    {
        $this->artisan('app:create-demo-accounts', ['--password' => 'pendek'])
            ->assertFailed();

        $this->assertSame(0, User::query()->count());
    }
}
