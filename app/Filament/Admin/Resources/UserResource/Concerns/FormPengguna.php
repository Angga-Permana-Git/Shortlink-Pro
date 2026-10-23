<?php

namespace App\Filament\Admin\Resources\UserResource\Concerns;

use App\Services\Time\UserTimezoneService;
use App\Support\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

trait FormPengguna
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')->label(__('Nama'))->required()->maxLength(255)->placeholder(__('cth: Budi Santoso')),
                TextInput::make('username')->label(__('Nama Pengguna'))->maxLength(255)->unique(ignoreRecord: true)->placeholder(__('cth: budi')),
                TextInput::make('email')->label(__('Email'))->email()->required()->maxLength(255)->unique(ignoreRecord: true)->placeholder(__('cth: budi@company.local')),
                Select::make('role')
                    ->label(__('Peran'))
                    ->options(Role::options())
                    ->default(Role::User->value)
                    ->required(),
                TextInput::make('password')
                    ->label(__('Kata Sandi'))
                    ->password()
                    ->revealable()
                    ->placeholder(__('Minimal diisi saat membuat akun'))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->maxLength(255),
                Select::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        1 => __('Aktif'),
                        0 => __('Nonaktif'),
                    ])
                    ->default(1)
                    ->required(),
                TextInput::make('auth_provider')
                    ->label(__('Sumber Akun'))
                    ->default('local')
                    ->placeholder(__('cth: local, keycloak'))
                    ->maxLength(50),
                Select::make('timezone')
                    ->label(__('Zona Waktu Pengguna'))
                    ->options(fn (): array => app(UserTimezoneService::class)->daftar())
                    ->searchable()
                    ->placeholder(__('Mengikuti zona waktu server')),
                TextInput::make('keycloak_sub')->label(__('ID Keycloak'))->placeholder(__('cth: uuid-dari-keycloak'))->maxLength(255),
                TextInput::make('sso_subject')->label(__('Subjek SSO'))->placeholder(__('cth: subject-12345'))->maxLength(255),
                TextInput::make('ldap_dn')->label(__('DN LDAP'))->placeholder(__('cth: cn=budi,ou=users,dc=company,dc=local'))->maxLength(255),
                Toggle::make('is_protected')
                    ->label(__('Akun dilindungi'))
                    ->helperText(__('Hanya akun lokal yang dapat mengubah akun terlindungi ini.')),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Informasi Akun'))
                    ->schema([
                        TextEntry::make('name')->label(__('Nama')),
                        TextEntry::make('username')->label(__('Nama Pengguna'))->placeholder(__('-')),
                        TextEntry::make('email')->label(__('Email'))->copyable(),
                        TextEntry::make('role')
                            ->label(__('Peran'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Role::tryFrom($state)?->label() ?? $state)
                            ->color(fn (string $state): string => $state === Role::Admin->value ? 'danger' : 'gray'),
                        TextEntry::make('is_active')
                            ->label(__('Status'))
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state ? __('Aktif') : __('Nonaktif'))
                            ->color(fn ($state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('auth_provider')
                            ->label(__('Sumber Akun'))
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'local' => __('Lokal'),
                                'keycloak' => 'Keycloak',
                                'sso' => 'SSO',
                                'ldap' => 'LDAP',
                                default => $state ?? __('-'),
                            }),
                        TextEntry::make('timezone')
                            ->label(__('Zona Waktu'))
                            ->formatStateUsing(fn (?string $state): string => $state
                                ? app(UserTimezoneService::class)->label($state).' ('.$state.')'
                                : __('Mengikuti server')),
                        TextEntry::make('is_protected')
                            ->label(__('Dilindungi'))
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state ? __('Dilindungi') : '-')
                            ->color(fn ($state): string => $state ? 'danger' : 'gray'),
                        TextEntry::make('last_login_at')->label(__('Login terakhir'))->dateTime()->placeholder(__('Belum pernah')),
                        TextEntry::make('created_at')->label(__('Terdaftar'))->dateTime(),
                        TextEntry::make('deleted_at')->label(__('Dihapus pada'))->dateTime()->placeholder(__('-')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
