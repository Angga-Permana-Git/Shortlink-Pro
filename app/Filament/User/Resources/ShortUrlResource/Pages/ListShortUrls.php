<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Filament\User\Resources\ShortUrlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShortUrls extends ListRecords
{
    protected static string $resource = ShortUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}