<?php

namespace Tests\Feature;

use App\Filament\User\Widgets\MyLinksOverview;
use App\Filament\Widgets\ActiveUsersTable;
use App\Filament\Widgets\AdminAttentionWidget;
use App\Filament\Widgets\AdminGrowthChart;
use App\Filament\Widgets\ClicksPerDayChart;
use App\Filament\Widgets\PopularLinksTable;
use App\Filament\Widgets\StatsOverview;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionProperty;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_widgets_render_with_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        ShortUrl::factory()->count(3)->create(['owner_id' => $admin->id]);

        $widget = Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertOk();

        $kolom = new ReflectionProperty(StatsOverview::class, 'columns');
        $this->assertSame(['@xl' => 4, '@lg' => 2, 'default' => 1], $kolom->getValue($widget->instance()));

        Livewire::actingAs($admin)
            ->test(ClicksPerDayChart::class)
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(AdminGrowthChart::class)
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(AdminAttentionWidget::class)
            ->assertOk()
            ->assertSee('Perlu Perhatian');

        Livewire::actingAs($admin)
            ->test(PopularLinksTable::class)
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(ActiveUsersTable::class)
            ->assertOk();
    }

    public function test_user_dashboard_widget_shows_only_own_links(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'user', 'is_active' => true]);

        ShortUrl::factory()->create(['owner_id' => $user->id]);
        ShortUrl::factory()->count(4)->create(['owner_id' => $other->id]);

        Livewire::actingAs($user)
            ->test(MyLinksOverview::class)
            ->assertOk()
            ->assertSee('Total Tautan');
    }
}
