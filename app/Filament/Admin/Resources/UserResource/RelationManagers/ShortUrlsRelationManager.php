<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use App\Filament\Admin\Resources\ShortUrlResource;
use App\Models\ShortUrl;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShortUrlsRelationManager extends RelationManager
{
    protected static string $relationship = 'shortUrls';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Tautan Pengguna');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->label(__('Slug'))->searchable()->copyable(),
                TextColumn::make('destination_url')->label(__('Tujuan'))->limit(45)->tooltip(fn (ShortUrl $record): string => $record->destination_url),
                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? __('Aktif') : __('Nonaktif'))
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('total_clicks')->label(__('Klik'))->numeric()->sortable(),
                TextColumn::make('expires_at')->label(__('Kedaluwarsa'))->dateTime()->placeholder(__('-')),
                TextColumn::make('created_at')->label(__('Dibuat'))->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('buka')
                    ->label(__('Buka'))
                    ->url(fn (ShortUrl $record): string => ShortUrlResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
