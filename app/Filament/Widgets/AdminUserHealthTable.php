<?php

namespace App\Filament\Widgets;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\AdminAnalyticsService;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AdminUserHealthTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Kesehatan Pengguna'))
            ->description(__('Akun yang perlu dihubungi atau ditinjau.'))
            ->query(app(AdminAnalyticsService::class)->penggunaPerluPerhatianQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('Pengguna'))
                    ->description(fn (User $record): string => (string) $record->email)
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
                    ->searchable(),
                TextColumn::make('is_active')->label(__('Status'))->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Aktif') : __('Nonaktif'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('tautan_aktif_count')->label(__('Tautan Aktif'))->badge(),
                IconColumn::make('two_factor_confirmed_at')->label(__('2FA'))->boolean(),
                TextColumn::make('timezone')->label(__('Zona Waktu'))->placeholder(__('Belum diisi')),
                TextColumn::make('created_at')->label(__('Terdaftar'))->dateTime('d M Y'),
            ])
            ->striped()
            ->paginated(false)
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateHeading(__('Semua pengguna sehat'))
            ->emptyStateDescription(__('Tidak ada akun yang perlu ditinjau.'));
    }
}
