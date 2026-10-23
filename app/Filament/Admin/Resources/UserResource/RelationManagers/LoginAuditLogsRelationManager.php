<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LoginAuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'loginAuditLogs';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Riwayat Masuk');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label(__('Waktu'))->dateTime()->sortable(),
                TextColumn::make('auth_provider')->label(__('Sumber'))->badge(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'success' ? __('Berhasil') : __('Gagal'))
                    ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger'),
                TextColumn::make('ip_address')->label(__('IP'))->placeholder(__('-')),
                TextColumn::make('user_agent')->label(__('Peramban'))->limit(40)->tooltip(fn ($state): ?string => $state),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
