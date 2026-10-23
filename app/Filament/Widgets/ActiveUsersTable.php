<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use App\Services\Time\UserTimezoneService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ActiveUsersTable extends TableWidget
{
    /** Dirender bersama halaman agar dashboard tidak menunggu permintaan Livewire per widget. */
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Peringkat Pengguna'))
            ->query(app(AdminDashboardService::class)->penggunaAktifQuery())
            ->columns([
                TextColumn::make('name')->label(__('Nama')),
                TextColumn::make('username')->label(__('Nama Pengguna')),
                TextColumn::make('short_urls_count')->label(__('Jumlah Tautan'))->sortable(),
                TextColumn::make('tautan_aktif_count')->label(__('Tautan Aktif'))->sortable(),
                TextColumn::make('short_urls_sum_total_clicks')->label(__('Total Klik'))->sortable()->badge(),
                TextColumn::make('klik_gagal_count')->label(__('Klik Gagal'))->sortable()->badge()->color('danger'),
                TextColumn::make('timezone')->label(__('Zona Waktu'))->formatStateUsing(fn (?string $state) => $state ? app(UserTimezoneService::class)->label($state) : __('Server')),
                TextColumn::make('last_login_at')->label(__('Login Terakhir'))->dateTime()->placeholder(__('Belum pernah')),
            ])
            ->defaultSort('short_urls_sum_total_clicks', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading(__('Belum ada pengguna aktif'))
            ->emptyStateDescription(__('Pengguna aktif akan tampil di sini.'));
    }
}
