<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Pages;

use App\Filament\Admin\Resources\ShortUrlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}