<?php

namespace App\Filament\User\Widgets;

use App\Models\ShortUrl;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyLinksOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $ownerId = Filament::auth()->id();

        $total = ShortUrl::where('owner_id', $ownerId)->count();
        $active = ShortUrl::where('owner_id', $ownerId)->where('is_active', true)->count();
        $clicks = ShortUrl::where('owner_id', $ownerId)->sum('total_clicks');
        $protected = ShortUrl::where('owner_id', $ownerId)->whereNotNull('password_hash')->count();

        return [
            Stat::make(__('ui.panel.total_tautan'), $total)
                ->icon('heroicon-o-link'),
            Stat::make(__('ui.panel.tautan_aktif'), $active)
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make(__('ui.panel.total_klik'), $clicks)
                ->icon('heroicon-o-cursor-arrow-rays')
                ->color('info'),
            Stat::make(__('ui.panel.tautan_terlindungi'), $protected)
                ->icon('heroicon-o-lock-closed')
                ->color('warning'),
        ];
    }
}
