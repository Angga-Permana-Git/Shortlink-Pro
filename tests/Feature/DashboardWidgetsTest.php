<?php

namespace Tests\Feature;

use App\Filament\Widgets\ClicksPerDayChart;
use App\Filament\Widgets\StatsOverview;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_widgets_render_with_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        ShortUrl::factory()->count(3)->create(['owner_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(ClicksPerDayChart::class)
            ->assertOk();
    }

    public function test_user_dashboard_widget_shows_only_own_links(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'user', 'is_active' => true]);

        ShortUrl::factory()->create(['owner_id' => $user->id]);
        ShortUrl::factory()->count(4)->create(['owner_id' => $other->id]);

        Livewire::actingAs($user)
            ->test(\App\Filament\User\Widgets\MyLinksOverview::class)
            ->assertOk()
            ->assertSee('Total Link');
    }
}