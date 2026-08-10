<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleChangeLogResource\Pages;
use App\Models\RoleChangeLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleChangeLogResource extends Resource
{
    protected static ?string $model = RoleChangeLog::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Role Logs';

    protected static ?string $modelLabel = 'Role Change Log';

    protected static ?string $pluralModelLabel = 'Role Change Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('actor.name')->label('Actor')
                    ->searchable()->placeholder('System'),
                TextColumn::make('target.name')->label('Target')->searchable(),
                TextColumn::make('old_role')->badge()->color('gray'),
                TextColumn::make('new_role')->badge()->color(fn ($state) => $state === 'admin' ? 'danger' : 'gray'),
                TextColumn::make('ip_address')->placeholder('None'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('new_role')
                    ->label('Role')
                    ->options(['user' => 'User', 'admin' => 'Admin']),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoleChangeLogs::route('/'),
        ];
    }
}