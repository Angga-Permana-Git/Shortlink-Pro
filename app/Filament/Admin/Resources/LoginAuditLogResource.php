<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LoginAuditLogResource\Pages;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\LoginAuditLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LoginAuditLogResource extends Resource
{
    use BahasaSumberDaya;

    protected static ?string $model = LoginAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $modelLabel = 'Log Masuk';

    protected static ?string $pluralModelLabel = 'Log Masuk';

    protected static string|UnitEnum|null $navigationGroup = 'Keamanan';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label(__('Pengguna'))
                    ->searchable()->placeholder(__('Tidak diketahui')),
                TextColumn::make('username')->label(__('Nama Pengguna'))
                    ->searchable(),
                TextColumn::make('auth_provider')->label(__('Penyedia'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'keycloak' => 'success',
                        'local' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('status')->badge()->label(__('Status'))
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address')->label(__('Alamat IP')),
                TextColumn::make('user_agent')
                    ->label(__('Perangkat'))
                    ->limit(40)
                    ->tooltip(fn ($state): string => $state ?? __('Tidak ada')),
                TextColumn::make('created_at')->dateTime()->sortable()->label(__('Waktu')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'success' => __('Berhasil'),
                        'failed' => __('Gagal'),
                    ]),
                SelectFilter::make('auth_provider')
                    ->label(__('Penyedia'))
                    ->options([
                        'local' => __('Lokal'),
                        'keycloak' => 'Keycloak',
                    ]),
                Filter::make('tanggal')
                    ->label(__('Rentang tanggal'))
                    ->schema([
                        DatePicker::make('dari')->label(__('Dari')),
                        DatePicker::make('sampai')->label(__('Sampai')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '>=', $tanggal))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '<=', $tanggal))),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Rincian Log Masuk'))
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('Pengguna'))
                            ->placeholder(__('Tidak diketahui')),
                        TextEntry::make('username')
                            ->label(__('Nama Pengguna'))
                            ->placeholder(__('-')),
                        TextEntry::make('auth_provider')
                            ->label(__('Penyedia'))
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'local' => __('Lokal'),
                                'keycloak' => 'Keycloak',
                                default => $state ?? __('-'),
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'keycloak' => 'success',
                                'local' => 'primary',
                                default => 'gray',
                            }),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'success' => __('Berhasil'),
                                'failed' => __('Gagal'),
                                default => $state ?? __('-'),
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'success' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('ip_address')
                            ->label(__('Alamat IP'))
                            ->placeholder(__('-')),
                        TextEntry::make('user_agent')
                            ->label(__('Perangkat'))
                            ->placeholder(__('Tidak ada'))
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label(__('Waktu'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoginAuditLogs::route('/'),
        ];
    }
}
