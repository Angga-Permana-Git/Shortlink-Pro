<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminSystemService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatusWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 12;

    protected int|array|null $columns = ['default' => 1, 'md' => 2, '@xl' => 5];

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $sistem = app(AdminSystemService::class)->ringkasan();

        return [
            Stat::make(__('Penjadwal'), $sistem['jadwal'])
                ->description(__('Denyut schedule:run setiap menit'))
                ->icon('heroicon-o-clock')
                ->color($sistem['jadwal_aktif'] ? 'success' : 'danger'),
            Stat::make(__('Antrean'), $sistem['antrean'])
                ->description(__(':jumlah tugas gagal', ['jumlah' => $sistem['antrean_gagal']]))
                ->icon('heroicon-o-queue-list')
                ->color($sistem['antrean_gagal'] > 0 ? 'danger' : 'success'),
            Stat::make(__('Retensi Log'), $sistem['retensi_log'])
                ->description(__('Dibersihkan otomatis tiap 02.00'))
                ->icon('heroicon-o-trash')
                ->color('info'),
            Stat::make(__('Ukuran Database'), $sistem['ukuran_database'])
                ->description(__('Mailer: :mailer', ['mailer' => $sistem['mailer']]))
                ->icon('heroicon-o-circle-stack')
                ->color('gray'),
            Stat::make(__('Versi Aplikasi'), $sistem['versi'])
                ->description(__('Laravel'))
                ->icon('heroicon-o-information-circle')
                ->color('gray'),
        ];
    }
}
