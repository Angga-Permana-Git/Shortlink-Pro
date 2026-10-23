<?php

namespace App\Filament\Admin\Resources\LoginAuditLogResource\Pages;

use App\Filament\Admin\Resources\LoginAuditLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListLoginAuditLogs extends ListRecords
{
    protected static string $resource = LoginAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ekspor')
                ->label(__('Ekspor CSV'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(route('admin.export', 'login')),
        ];
    }
}
