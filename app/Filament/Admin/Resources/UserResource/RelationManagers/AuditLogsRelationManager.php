<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Jejak Audit');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label(__('Waktu'))->dateTime()->sortable(),
                TextColumn::make('action')->label(__('Aksi'))->badge()->searchable(),
                TextColumn::make('resource_type')->label(__('Objek'))->placeholder(__('-')),
                TextColumn::make('ip_address')->label(__('IP'))->placeholder(__('-')),
                TextColumn::make('metadata')
                    ->label(__('Rincian'))
                    ->formatStateUsing(fn ($state): string => filled($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : '-')
                    ->limit(50)
                    ->tooltip(fn ($state): ?string => filled($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : null),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
