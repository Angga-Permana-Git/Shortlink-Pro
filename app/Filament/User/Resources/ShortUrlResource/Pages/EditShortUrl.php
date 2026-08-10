<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Filament\User\Resources\ShortUrlResource;
use App\Services\ShortUrl\ShortUrlService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditShortUrl extends EditRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $protected = (bool) ($data['_protected'] ?? false);

        $data['protected'] = $protected;
        $data['password'] = $protected ? ($data['_password'] ?? null) : null;

        unset($data['_protected'], $data['_password'], $data['_slug'], $data['password_hash']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $shortUrl = app(ShortUrlService::class)->update(
            $record,
            auth()->user(),
            $data,
        );

        Notification::make()
            ->success()
            ->title('Link updated')
            ->send();

        return $shortUrl;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}