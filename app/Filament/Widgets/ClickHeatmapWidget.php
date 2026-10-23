<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminAnalyticsService;
use App\Support\Admin\DashboardPeriod;
use Filament\Widgets\Widget;

class ClickHeatmapWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.click-heatmap';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return ['peta' => app(AdminAnalyticsService::class)->heatmap(DashboardPeriod::hari())];
    }
}
