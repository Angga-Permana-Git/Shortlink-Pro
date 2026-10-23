<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Dto\UpdateShortUrlDTO;
use App\Filament\User\Resources\ShortUrlResource;
use App\Services\ShortUrl\ShortUrlService;
use App\Services\Time\UserTimezoneService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditShortUrl extends EditRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $waktu = app(UserTimezoneService::class);
        $pengguna = auth()->user();

        $data['starts_at'] = $waktu->keLokal($data['starts_at'] ?? null, $pengguna);
        $data['expires_at'] = $waktu->keLokal($data['expires_at'] ?? null, $pengguna);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $protected = (bool) ($data['_protected'] ?? false);
        $waktu = app(UserTimezoneService::class);
        $pengguna = auth()->user();

        $data['protected'] = $protected;
        $data['password'] = $protected ? ($data['_password'] ?? null) : null;
        $data['starts_at'] = $waktu->keServer($data['starts_at'] ?? null, $pengguna);
        $data['expires_at'] = $waktu->keServer($data['expires_at'] ?? null, $pengguna);

        unset($data['_protected'], $data['_password'], $data['_slug'], $data['password_hash']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $shortUrl = app(ShortUrlService::class)->update(
            $record,
            auth()->user(),
            UpdateShortUrlDTO::fromArray($data),
        );

        Notification::make()
            ->success()
            ->title('Tautan berhasil diubah')
            ->send();

        return $shortUrl;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
