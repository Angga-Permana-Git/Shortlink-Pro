<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LoginAuditLogResource\Pages;
use App\Models\LoginAuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoginAuditLogResource extends Resource
{
    protected static ?string $model = LoginAuditLog::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $modelLabel = 'Login Log';

    protected static ?string $pluralModelLabel = 'Login Logs';

    protected static ?string $navigationGroup = 'Security';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('User')
                    ->searchable()->placeholder('Unknown'),
                TextColumn::make('username')->label('Username')
                    ->searchable(),
                TextColumn::make('auth_provider')->label('Provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'keycloak' => 'success',
                        'local' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address'),
                TextColumn::make('user_agent')
                    ->limit(40)
                    ->tooltip(fn ($state): string => $state ?? 'N/A'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('auth_provider')
                    ->label('Provider')
                    ->options([
                        'local' => 'Local',
                        'keycloak' => 'Keycloak',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoginAuditLogs::route('/'),
        ];
    }
}
