<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PopularLinksTable extends TableWidget
{
    /** Dirender bersama halaman agar dashboard tidak menunggu permintaan Livewire per widget. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Tautan Terpopuler'))
            ->query(app(AdminDashboardService::class)->tautanTerpopulerQuery())
            ->columns([
                TextColumn::make('slug')->label(__('Nama Pendek'))->copyable(),
                TextColumn::make('owner.username')->label(__('Pemilik'))->placeholder('—'),
                TextColumn::make('total_clicks')->label(__('Total Klik'))->sortable()->badge(),
                TextColumn::make('last_clicked_at')->label(__('Klik Terakhir'))->dateTime()->placeholder(__('Belum pernah')),
            ])
            ->striped()
            ->paginated(false)
            ->emptyStateIcon('heroicon-o-link')
            ->emptyStateHeading(__('Belum ada tautan'))
            ->emptyStateDescription(__('Tautan yang dibuat pengguna akan tampil di sini.'));
    }
}
