<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleChangeLogResource\Pages;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\RoleChangeLog;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleChangeLogResource extends Resource
{
    use BahasaSumberDaya;

    protected static ?string $model = RoleChangeLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Log Peran';

    protected static ?string $modelLabel = 'Log Perubahan Peran';

    protected static ?string $pluralModelLabel = 'Log Perubahan Peran';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable()->label(__('Waktu')),
                TextColumn::make('actor.name')->label(__('Pelaku'))
                    ->searchable()->placeholder(__('Sistem')),
                TextColumn::make('target.name')->label(__('Target'))->searchable(),
                TextColumn::make('old_role')->badge()->color('gray')->label(__('Peran Lama')),
                TextColumn::make('new_role')->badge()->color(fn ($state) => $state === 'admin' ? 'danger' : 'gray')->label(__('Peran Baru')),
                TextColumn::make('ip_address')->label(__('Alamat IP'))->placeholder(__('Tidak ada')),
            ])
            ->filters([
                SelectFilter::make('new_role')
                    ->label(__('Peran'))
                    ->options(['user' => 'User', 'admin' => 'Admin']),
                Filter::make('tanggal')
                    ->label(__('Rentang tanggal'))
                    ->schema([
                        DatePicker::make('dari')->label(__('Dari')),
                        DatePicker::make('sampai')->label(__('Sampai')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '>=', $tanggal))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '<=', $tanggal))),
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
