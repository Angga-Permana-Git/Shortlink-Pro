<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClickEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'clicks';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Riwayat Klik');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label(__('Waktu'))->dateTime()->sortable(),
                TextColumn::make('status')
                    ->label(__('Hasil'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => __('Berhasil'),
                        'wrong_password' => __('Kata sandi salah'),
                        'expired' => __('Kedaluwarsa'),
                        'scheduled' => __('Belum aktif'),
                        'inactive' => __('Nonaktif'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger'),
                TextColumn::make('ip_hash')->label(__('IP (hash)'))->limit(16)->tooltip(fn ($state): ?string => $state),
                TextColumn::make('referer')->label(__('Perujuk'))->limit(40)->placeholder(__('-'))->tooltip(fn ($state): ?string => $state),
                TextColumn::make('user_agent')->label(__('Peramban'))->limit(40)->placeholder(__('-'))->tooltip(fn ($state): ?string => $state),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
