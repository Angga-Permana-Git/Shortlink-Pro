<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivityTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Aktivitas Terbaru'))
            ->description(__('Jejak perubahan penting dari audit log.'))
            ->query(app(AdminAnalyticsService::class)->aktivitasTerbaru())
            ->columns([
                TextColumn::make('created_at')->label(__('Waktu'))->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('actor.name')->label(__('Aktor'))->placeholder(__('Sistem')),
                TextColumn::make('action')->label(__('Aksi'))->badge()->formatStateUsing(fn (string $state): string => str_replace(['.', '_'], [' · ', ' '], $state)),
                TextColumn::make('resource_type')->label(__('Objek'))->placeholder('—'),
                TextColumn::make('resource_id')->label(__('ID'))->placeholder('—'),
                TextColumn::make('ip_address')->label(__('IP'))->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->paginated(false)
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading(__('Belum ada aktivitas'))
            ->emptyStateDescription(__('Perubahan penting akan muncul di sini.'));
    }
}
