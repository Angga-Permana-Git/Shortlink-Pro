<?php

namespace App\Filament\Admin\Resources\ClickEventResource\Pages;

use App\Filament\Admin\Resources\ClickEventResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListClickEvents extends ListRecords
{
    protected static string $resource = ClickEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ekspor')
                ->label(__('Ekspor CSV'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(route('admin.export', 'klik')),
        ];
    }
}
