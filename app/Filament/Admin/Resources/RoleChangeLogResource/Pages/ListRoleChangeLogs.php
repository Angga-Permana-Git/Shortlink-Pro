<?php

namespace App\Filament\Admin\Resources\RoleChangeLogResource\Pages;

use App\Filament\Admin\Resources\RoleChangeLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRoleChangeLogs extends ListRecords
{
    protected static string $resource = RoleChangeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ekspor')
                ->label(__('Ekspor CSV'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(route('admin.export', 'peran')),
        ];
    }
}
