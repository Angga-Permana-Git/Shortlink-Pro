<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use App\Support\Admin\DashboardPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    /** Dirender bersama halaman agar dashboard tidak menunggu permintaan Livewire per widget. */
    protected static bool $isLazy = false;

    /** Grid ringkas: satu kolom di ponsel, dua di layar sedang, empat di layar besar. */
    protected static ?int $sort = 1;

    protected int|array|null $columns = [
        '@xl' => 4,
        '@lg' => 2,
        'default' => 1,
    ];

    protected function getStats(): array
    {
        $hari = DashboardPeriod::hari();
        $ringkasan = app(AdminDashboardService::class)->ringkasan($hari);

        return [
            Stat::make(__('Pengguna Aktif'), $ringkasan['pengguna_aktif'])
                ->description(__(':jumlah baru / :hari hari', ['jumlah' => $ringkasan['pengguna_baru'], 'hari' => $hari]))
                ->icon('heroicon-o-users')
                ->color('info'),
            Stat::make(__('Pengguna Baru'), $ringkasan['pengguna_baru'])
                ->description($this->delta($ringkasan['pengguna_baru'], $ringkasan['pengguna_baru_sebelum']))
                ->icon('heroicon-o-user-plus')
                ->color('success'),
            Stat::make(__('Total Tautan'), $ringkasan['tautan'])
                ->description(__(':jumlah baru / :hari hari', ['jumlah' => $ringkasan['tautan_baru'], 'hari' => $hari]))
                ->icon('heroicon-o-link')
                ->color('primary'),
            Stat::make(__('Tautan Aktif'), $ringkasan['tautan_aktif'])
                ->description(__('Sesuai jadwal dan status'))
                ->icon('heroicon-o-check-badge')
                ->color('success'),
            Stat::make(__('Total Klik'), $ringkasan['klik'])
                ->description(__('Akses tercatat'))
                ->icon('heroicon-o-cursor-arrow-rays')
                ->color('success'),
            Stat::make(__('Klik :hari Hari', ['hari' => $hari]), $ringkasan['klik_periode'])
                ->description($this->delta($ringkasan['klik_periode'], $ringkasan['klik_sebelum']))
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),
            Stat::make(__('Linktree Terbit'), $ringkasan['linktree_terbit'])
                ->description(__('Profil dengan alamat publik'))
                ->icon('heroicon-o-globe-alt')
                ->color('info'),
            Stat::make(__('Profil Tanpa Alamat'), $ringkasan['tanpa_alamat'])
                ->description(__('Perlu dilengkapi pemilik'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color($ringkasan['tanpa_alamat'] > 0 ? 'danger' : 'gray'),
        ];
    }

    private function delta(int $sekarang, int $sebelum): string
    {
        if ($sebelum === 0) {
            return __($sekarang > 0 ? 'Baru pada periode ini' : 'Belum ada data pembanding');
        }

        $persen = (int) round((($sekarang - $sebelum) / $sebelum) * 100);

        return __(($persen >= 0 ? '↑ ' : '↓ ').':persen% vs periode sebelumnya', ['persen' => abs($persen)]);
    }
}
