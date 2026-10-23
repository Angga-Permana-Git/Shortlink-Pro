<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminAnalyticsService;
use Filament\Widgets\Widget;

class DestinationDomainsWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.destination-domains';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return ['domain' => app(AdminAnalyticsService::class)->domainTujuan()];
    }
}
