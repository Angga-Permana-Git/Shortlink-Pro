<?php

namespace App\Filament\Widgets;

use App\Support\Admin\DashboardPeriod;
use Filament\Widgets\Widget;

class DashboardPeriodFilter extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-period-filter';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'hariIni' => DashboardPeriod::hari(),
            'pilihan' => DashboardPeriod::PILIHAN,
        ];
    }
}
