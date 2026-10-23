<?php

namespace App\Filament\Admin\Resources\LinktreeProfileResource\Pages;

use App\Filament\Admin\Resources\LinktreeProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditLinktreeProfile extends EditRecord
{
    protected static string $resource = LinktreeProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
