<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClickEventResource\Pages;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\ClickEvent;
use App\Support\Enums\ClickStatus;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClickEventResource extends Resource
{
    use BahasaSumberDaya;

    protected static ?string $model = ClickEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $modelLabel = 'Log Klik';

    protected static ?string $pluralModelLabel = 'Log Klik';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('shortUrl.slug')->label(__('Tautan Pendek'))->searchable()
                    ->url(fn (ClickEvent $record) => route('redirect.show', $record->shortUrl->slug)),
                TextColumn::make('status')->badge()->label(__('Status'))
                    ->color(fn ($state) => $state === ClickStatus::Success->value ? 'success' : 'danger'),
                TextColumn::make('ip_hash')->label(__('IP (hash)'))->limit(20),
                TextColumn::make('referer')->limit(40)->placeholder(__('Tidak ada'))->label(__('Perujuk')),
                TextColumn::make('user_agent')->limit(40)->placeholder(__('Tidak ada'))->label(__('Perangkat')),
                TextColumn::make('created_at')->dateTime()->sortable()->label(__('Waktu')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(fn () => collect(ClickStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClickEvents::route('/'),
        ];
    }
}
