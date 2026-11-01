<?php

namespace App\Filament\Admin\Resources\BioProfileResource\Pages;

use App\Filament\Admin\Resources\BioProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditBioProfile extends EditRecord
{
    protected static string $resource = BioProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
