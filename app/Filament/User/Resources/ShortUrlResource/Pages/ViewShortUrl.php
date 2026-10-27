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
                TextEntry::make('slug')->label(__('ui.panel.model_tautan')),
                TextEntry::make('destination_url')->label(__('ui.panel.url_tujuan'))
                    ->copyable(),
                IconEntry::make('is_active')->label(__('ui.panel.aktif'))->boolean(),
                TextEntry::make('total_clicks')->label(__('ui.panel.total_klik')),
                TextEntry::make('last_clicked_at')->label(__('ui.panel.klik_terakhir'))
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->last_clicked_at, auth()->user()))
                    ->placeholder(__('ui.panel.belum_pernah')),
                TextEntry::make('starts_at')->label(__('ui.panel.mulai_aktif'))
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->starts_at, auth()->user())),
                TextEntry::make('expires_at')->label(__('ui.panel.kedaluwarsa'))
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->expires_at, auth()->user())),
                TextEntry::make('has_password')
                    ->label(__('ui.panel.dilindungi_sandi'))
                    ->formatStateUsing(fn ($record) => $record->hasPassword() ? __('ui.panel.ya') : __('ui.panel.tidak')),
            ]);
    }
}
