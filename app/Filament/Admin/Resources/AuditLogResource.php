<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditLogResource\Pages;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\AuditLog;
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

class AuditLogResource extends Resource
{
    use BahasaSumberDaya;

    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Log Audit';

    protected static ?string $pluralModelLabel = 'Log Audit';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('actor.name')->label(__('Aktor'))
                    ->searchable()->placeholder(__('Sistem')),
                TextColumn::make('action')->badge()->color('primary')->label(__('Aksi')),
                TextColumn::make('resource_type')->label(__('Jenis Sumber')),
                TextColumn::make('resource_id')->label(__('ID Sumber')),
                TextColumn::make('metadata')
                    ->label(__('Detail'))
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state)
                    ->limit(60)
                    ->placeholder(__('Tidak ada')),
                TextColumn::make('ip_address')->label(__('Alamat IP'))->placeholder(__('Tidak ada')),
                TextColumn::make('created_at')->dateTime()->sortable()->label(__('Waktu')),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label(__('Aksi'))
                    ->options(fn () => AuditLog::query()
                        ->select('action')
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all()),
                SelectFilter::make('resource_type')
                    ->label(__('Jenis Sumber'))
                    ->options(fn () => AuditLog::query()
                        ->whereNotNull('resource_type')
                        ->select('resource_type')
                        ->distinct()
                        ->orderBy('resource_type')
                        ->pluck('resource_type', 'resource_type')
                        ->all()),
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
                Section::make(__('Rincian Log Audit'))
                    ->schema([
                        TextEntry::make('id')->label(__('ID')),
                        TextEntry::make('actor.name')
                            ->label(__('Aktor'))
                            ->placeholder(__('Sistem')),
                        TextEntry::make('action')
                            ->label(__('Aksi'))
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('resource_type')
                            ->label(__('Jenis Sumber'))
                            ->placeholder(__('-')),
                        TextEntry::make('resource_id')
                            ->label(__('ID Sumber'))
                            ->placeholder(__('-')),
                        TextEntry::make('metadata')
                            ->label(__('Detail'))
                            ->formatStateUsing(fn ($state): string => filled($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : __('Tidak ada'))
                            ->columnSpanFull(),
                        TextEntry::make('ip_address')
                            ->label(__('Alamat IP'))
                            ->placeholder(__('Tidak ada')),
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
