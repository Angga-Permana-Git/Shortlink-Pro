<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClickEventResource\Pages;
use App\Models\ClickEvent;
use App\Support\Enums\ClickStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClickEventResource extends Resource
{
    protected static ?string $model = ClickEvent::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $modelLabel = 'Click Event';

    protected static ?string $pluralModelLabel = 'Click Events';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('shortUrl.slug')->label('Short URL')->searchable()
                    ->url(fn (ClickEvent $record) => route('redirect.show', $record->shortUrl->slug)),
                TextColumn::make('status')->badge()
                    ->color(fn ($state) => $state === ClickStatus::Success->value ? 'success' : 'danger'),
                TextColumn::make('ip_hash')->label('IP (hashed)')->limit(20),
                TextColumn::make('referer')->limit(40)->placeholder('None'),
                TextColumn::make('user_agent')->limit(40)->placeholder('None'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(fn () => collect(ClickStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClickEvents::route('/'),
        ];
    }
}