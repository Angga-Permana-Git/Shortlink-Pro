<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use App\Services\Branding\BrandingService;
use App\Support\Admin\DashboardPeriod;
use Filament\Widgets\ChartWidget;

class AdminGrowthChart extends ChartWidget
{
    /** Dirender bersama halaman agar dashboard tidak menunggu permintaan Livewire per widget. */
    protected static bool $isLazy = false;

    protected ?string $heading = 'Pertumbuhan';

    protected ?string $description = 'Tautan dan pengguna baru per hari.';

    protected static ?int $sort = 4;

    /** Penuh di ponsel, setengah lebar di layar besar agar berdampingan dengan grafik klik. */
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected ?string $maxHeight = '16rem';

    protected function getData(): array
    {
        $hari = DashboardPeriod::hari();
        $this->heading = __('Pertumbuhan · :hari Hari', ['hari' => $hari]);
        $this->description = __('Tautan dan pengguna baru per hari.');
        $data = app(AdminDashboardService::class)->pertumbuhan($hari);
        $warna = app(BrandingService::class)->brandColor();

        return [
            'datasets' => [
                [
                    'label' => __('Tautan baru'),
                    'data' => $data['tautan'],
                    'backgroundColor' => $warna,
                    'borderColor' => $warna,
                ],
                // Deret kedua dibedakan lewat garis putus-putus, bukan warna mentah baru,
                // sehingga tetap terbaca tanpa bergantung pada warna saja.
                [
                    'label' => __('Pengguna baru'),
                    'data' => $data['pengguna'],
                    'backgroundColor' => $warna,
                    'borderColor' => $warna,
                    'borderDash' => [6, 4],
                ],
            ],
            'labels' => collect(range($hari - 1, 0))->map(fn ($offset) => now()->subDays($offset)->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
