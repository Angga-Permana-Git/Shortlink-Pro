<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIpAllowlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_allowlist_permits_every_ip(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_ip_outside_allowlist_is_forbidden(): void
    {
        Setting::set('admin_ip_allowlist', ['10.0.0.1']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_cidr_and_exact_ip_are_supported(): void
    {
        Setting::set('admin_ip_allowlist', ['127.0.0.0/8']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        Setting::set('admin_ip_allowlist', ['10.0.0.0/24']);
        $this->actingAs($admin)
            ->get('/admin')
            ->assertForbidden();
    }
}
