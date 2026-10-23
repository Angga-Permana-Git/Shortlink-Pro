<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Concerns;

use App\Models\ShortUrl;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

trait DetailTautan
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Informasi Tautan'))
                    ->schema([
                        TextEntry::make('slug')->label(__('Nama Pendek'))->copyable(),
                        TextEntry::make('destination_url')->label(__('URL Tujuan'))->copyable()->columnSpanFull(),
                        TextEntry::make('owner.username')->label(__('Pemilik'))->placeholder(__('-')),
                        TextEntry::make('is_active')
                            ->label(__('Status'))
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state ? __('Aktif') : __('Nonaktif'))
                            ->color(fn ($state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('starts_at')->label(__('Mulai aktif'))->dateTime()->placeholder(__('Langsung')),
                        TextEntry::make('expires_at')->label(__('Kedaluwarsa'))->dateTime()->placeholder(__('Selamanya')),
                        TextEntry::make('password_hash')
                            ->label(__('Kata Sandi'))
                            ->formatStateUsing(fn ($state): string => filled($state) ? __('Dilindungi') : __('Tidak')),
                        TextEntry::make('created_at')->label(__('Dibuat'))->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('Analitik Klik'))
                    ->schema([
                        TextEntry::make('total_clicks')->label(__('Total klik')),
                        TextEntry::make('klik_unik')
                            ->label(__('Klik unik'))
                            ->state(fn (ShortUrl $record): int => $record->clicks()->distinct('ip_hash')->count('ip_hash')),
                        TextEntry::make('klik_gagal')
                            ->label(__('Percobaan kata sandi salah'))
                            ->state(fn (ShortUrl $record): int => $record->clicks()->where('status', 'wrong_password')->count()),
                        TextEntry::make('last_clicked_at')->label(__('Klik terakhir'))->dateTime()->placeholder(__('Belum pernah')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
