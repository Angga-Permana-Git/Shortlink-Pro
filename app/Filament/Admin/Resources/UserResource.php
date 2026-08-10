<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\Enums\Role;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')->required()->maxLength(255)->placeholder('cth: Budi Santoso'),
                TextInput::make('username')->maxLength(255)->unique(ignoreRecord: true)->placeholder('cth: budi'),
                TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true)->placeholder('cth: budi@company.local'),
                Select::make('role')
                    ->options(Role::options())
                    ->default(Role::User->value)
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->placeholder('Minimal diisi saat membuat akun')
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->maxLength(255),
                Select::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->required(),
                TextInput::make('auth_provider')
                    ->default('local')
                    ->placeholder('cth: local, keycloak')
                    ->maxLength(50),
                TextInput::make('keycloak_sub')->placeholder('cth: uuid-dari-keycloak')->maxLength(255),
                TextInput::make('sso_subject')->placeholder('cth: subject-12345')->maxLength(255),
                TextInput::make('ldap_dn')->placeholder('cth: cn=budi,ou=users,dc=company,dc=local')->maxLength(255),
                \Filament\Forms\Components\Toggle::make('is_protected')
                    ->label('Protected account')
                    ->helperText('Hanya akun lokal yang dapat mengubah akun protected ini.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('username')->searchable()->label('Username'),
                TextColumn::make('auth_provider')->badge()->sortable(),
                TextColumn::make('role')->badge()->color(fn ($state) => $state === Role::Admin->value ? 'danger' : 'gray'),
                TextColumn::make('is_protected')
                    ->label('Protected')
                    ->badge()
                    ->colors([
                        'danger' => true,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Protected' : '-'),
                ToggleColumn::make('is_active')->label('Active'),
                TextColumn::make('last_login_at')->dateTime()->sortable()->placeholder('Never'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(Role::options()),
                SelectFilter::make('auth_provider')->label('Sumber Akun')
                    ->options(['keycloak' => 'Keycloak', 'local' => 'Local']),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->is_protected === false)
                    ->hidden(fn ($record) => $record->is_protected),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}