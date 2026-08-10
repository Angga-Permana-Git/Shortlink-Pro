<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Pages;

use App\Filament\Admin\Resources\ShortUrlResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditShortUrl extends EditRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $passwordHash = $this->record->password_hash;

        if (! empty($data['remove_password'])) {
            $passwordHash = null;
        }

        if (! empty($data['new_password'])) {
            $passwordHash = Hash::make($data['new_password']);
        }

        $data['password_hash'] = $passwordHash;

        unset($data['new_password'], $data['remove_password']);

        return $data;
    }
}