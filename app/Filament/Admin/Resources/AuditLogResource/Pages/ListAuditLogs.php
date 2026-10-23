<?php

namespace App\Filament\Admin\Resources\AuditLogResource\Pages;

use App\Filament\Admin\Resources\AuditLogResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ekspor')
                ->label(__('Ekspor CSV'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(route('admin.export', 'audit')),
        ];
    }
}
