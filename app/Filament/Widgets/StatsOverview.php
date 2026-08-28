<?php

namespace App\Filament\Widgets;

use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalLinks = ShortUrl::count();
        $totalClicks = ShortUrl::sum('total_clicks');
        $totalUsers = User::count();
        $clicksToday = ClickEvent::whereDate('created_at', today())->count();

        $linksTrend = DB::table('short_urls')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value) => (float) $value)
            ->values()
            ->all();

        $clicksTrend = DB::table('click_events')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value) => (float) $value)
            ->values()
            ->all();

        return [
            Stat::make('Total Links', $totalLinks)
                ->description('Semua short link')
                ->icon('heroicon-o-link')
                ->chart($linksTrend),
            Stat::make('Total Clicks', $totalClicks)
                ->description('Akses tercatat')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->color('success')
                ->chart($clicksTrend),
            Stat::make('Pengguna', $totalUsers)
                ->description('Akun terdaftar')
                ->icon('heroicon-o-users')
                ->color('info'),
            Stat::make('Klik Hari Ini', $clicksToday)
                ->description(now()->format('d M Y'))
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
