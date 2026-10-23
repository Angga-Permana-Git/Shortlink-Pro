<?php

namespace App\Filament\Widgets;

use App\Models\ClickEvent;
use App\Services\Branding\BrandingService;
use App\Support\Admin\DashboardPeriod;
use App\Support\Enums\ClickStatus;
use Filament\Widgets\ChartWidget;

class ClicksPerDayChart extends ChartWidget
{
    /** Dirender bersama halaman agar dashboard tidak menunggu permintaan Livewire per widget. */
    protected static bool $isLazy = false;

    protected ?string $heading = 'Klik Sukses';

    protected ?string $description = 'Jumlah tautan yang berhasil dibuka setiap hari.';

    protected static ?int $sort = 2;

    /** Penuh di ponsel, setengah lebar di layar besar agar berdampingan dengan grafik pertumbuhan. */
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected ?string $maxHeight = '16rem';

    protected function getData(): array
    {
        $hari = DashboardPeriod::hari();
        $this->heading = __('Klik Sukses · :hari Hari', ['hari' => $hari]);
        $this->description = __('Jumlah tautan yang berhasil dibuka setiap hari.');
        $tanggal = collect(range($hari - 1, 0))
            ->map(fn (int $offset) => now()->subDays($offset)->startOfDay());

        $jumlah = ClickEvent::query()
            ->where('status', ClickStatus::Success->value)
            ->where('created_at', '>=', now()->subDays($hari - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->pluck('jumlah', 'tanggal');

        $warna = app(BrandingService::class)->brandColor();

        return [
            'datasets' => [
                [
                    'label' => __('Klik sukses'),
                    'data' => $tanggal->map(fn ($hari): int => (int) ($jumlah[$hari->toDateString()] ?? 0))->all(),
                    'backgroundColor' => $warna,
                    'borderColor' => $warna,
                ],
            ],
            'labels' => $tanggal->map(fn ($hari): string => $hari->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
