<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Filament\User\Resources\ShortUrlResource;
use Filament\Resources\Pages\ViewRecord;

class ViewShortUrl extends ViewRecord
{
    protected static string $resource = ShortUrlResource::class;

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('slug')->label('Short URL'),
                \Filament\Infolists\Components\TextEntry::make('destination_url')->label('Destination URL')
                    ->copyable(),
                \Filament\Infolists\Components\IconEntry::make('is_active')->label('Active')->boolean(),
                \Filament\Infolists\Components\TextEntry::make('total_clicks')->label('Total clicks'),
                \Filament\Infolists\Components\TextEntry::make('last_clicked_at')->label('Last clicked')
                    ->dateTime()->placeholder('Never'),
                \Filament\Infolists\Components\TextEntry::make('starts_at')->label('Starts at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('expires_at')->label('Expires at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('has_password')
                    ->label('Password protected')
                    ->formatStateUsing(fn ($record) => $record->hasPassword() ? 'Yes' : 'No'),
            ]);
    }
}