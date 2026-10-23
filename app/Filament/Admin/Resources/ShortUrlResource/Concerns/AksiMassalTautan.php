<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Concerns;

use App\Models\ShortUrl;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

trait AksiMassalTautan
{
    /** @return array<int, BulkAction> */
    protected static function aksiMassalTautan(): array
    {
        return [
            BulkAction::make('aktifkan')
                ->label(__('Aktifkan'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->action(function (Collection $records): void {
                    $records->each(fn (ShortUrl $tautan) => $tautan->update(['is_active' => true]));

                    Notification::make()->success()->title(__(':jumlah tautan diaktifkan', ['jumlah' => $records->count()]))->send();
                }),
            BulkAction::make('nonaktifkan')
                ->label(__('Nonaktifkan'))
                ->icon(Heroicon::OutlinedPauseCircle)
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->action(function (Collection $records): void {
                    $records->each(fn (ShortUrl $tautan) => $tautan->update(['is_active' => false]));

                    Notification::make()->success()->title(__(':jumlah tautan dinonaktifkan', ['jumlah' => $records->count()]))->send();
                }),
            BulkAction::make('perpanjang')
                ->label(__('Perpanjang'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->form([
                    DatePicker::make('expires_at')
                        ->label(__('Kedaluwarsa baru'))
                        ->required()
                        ->after('now'),
                ])
                ->deselectRecordsAfterCompletion()
                ->action(function (Collection $records, array $data): void {
                    $records->each(fn (ShortUrl $tautan) => $tautan->update(['expires_at' => $data['expires_at']]));

                    Notification::make()->success()->title(__(':jumlah tautan diperpanjang', ['jumlah' => $records->count()]))->send();
                }),
            BulkAction::make('pindahkan_pemilik')
                ->label(__('Pindahkan Pemilik'))
                ->icon(Heroicon::OutlinedArrowPathRoundedSquare)
                ->form([
                    Select::make('owner_id')
                        ->label(__('Pemilik baru'))
                        ->options(fn (): array => User::orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                ])
                ->deselectRecordsAfterCompletion()
                ->action(function (Collection $records, array $data): void {
                    $records->each(fn (ShortUrl $tautan) => $tautan->update(['owner_id' => $data['owner_id']]));

                    Notification::make()->success()->title(__(':jumlah tautan dipindahkan', ['jumlah' => $records->count()]))->send();
                }),
        ];
    }
}
