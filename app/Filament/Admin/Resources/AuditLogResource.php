<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('actor.name')->label('Actor')
                    ->searchable()->placeholder('System'),
                TextColumn::make('action')->badge()->color('primary'),
                TextColumn::make('resource_type')->label('Resource'),
                TextColumn::make('resource_id')->label('ID'),
                TextColumn::make('metadata')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state)
                    ->limit(60)
                    ->placeholder('None'),
                TextColumn::make('ip_address')->placeholder('None'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}