<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Filament\User\Resources\ShortUrlResource;
use App\Models\ShortUrl;
use App\Services\Time\UserTimezoneService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewShortUrl extends ViewRecord
{
    protected static string $resource = ShortUrlResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('slug')->label('Tautan Pendek'),
                TextEntry::make('destination_url')->label('URL Tujuan')
                    ->copyable(),
                IconEntry::make('is_active')->label('Aktif')->boolean(),
                TextEntry::make('total_clicks')->label('Total klik'),
                TextEntry::make('last_clicked_at')->label('Klik terakhir')
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->last_clicked_at, auth()->user()))
                    ->placeholder('Belum pernah'),
                TextEntry::make('starts_at')->label('Mulai aktif')
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->starts_at, auth()->user())),
                TextEntry::make('expires_at')->label('Kedaluwarsa')
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->expires_at, auth()->user())),
                TextEntry::make('has_password')
                    ->label('Dilindungi kata sandi')
                    ->formatStateUsing(fn ($record) => $record->hasPassword() ? 'Ya' : 'Tidak'),
            ]);
    }
}
