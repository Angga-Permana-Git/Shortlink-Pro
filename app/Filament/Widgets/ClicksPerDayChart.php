<?php

namespace App\Filament\Widgets;

use App\Models\ClickEvent;
use App\Support\Enums\ClickStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ClicksPerDayChart extends ChartWidget
{
    protected ?string $heading = 'Akses 14 Hari Terakhir';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(function ($offset) {
            $date = now()->subDays($offset)->toDateString();

            $count = ClickEvent::query()
                ->whereDate('created_at', $date)
                ->where('status', ClickStatus::Success->value)
                ->count();

            return [$date, $count];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Klik sukses',
                    'data' => $days->pluck(1)->all(),
                    'backgroundColor' => '#2563EB',
                    'borderColor' => '#2563EB',
                ],
            ],
            'labels' => $days->pluck(0)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}