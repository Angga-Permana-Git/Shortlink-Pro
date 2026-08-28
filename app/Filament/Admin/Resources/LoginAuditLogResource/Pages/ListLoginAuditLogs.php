<?php

namespace App\Filament\Admin\Resources\LoginAuditLogResource\Pages;

use App\Filament\Admin\Resources\LoginAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListLoginAuditLogs extends ListRecords
{
    protected static string $resource = LoginAuditLogResource::class;
}
