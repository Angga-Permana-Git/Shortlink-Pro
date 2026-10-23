<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminAttentionService;
use Filament\Widgets\Widget;

/**
 * Daftar tindakan admin dalam bentuk kartu ringkas.
 *
 * Sengaja bukan StatsOverviewWidget: jumlah peringatan bisa banyak dan kartu
 * statistik akan membuat dashboard memanjang tanpa hierarki yang jelas.
 */
class AdminAttentionWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-attention';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return ['items' => app(AdminAttentionService::class)->peringatan()];
    }
}
