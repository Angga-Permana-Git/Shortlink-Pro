<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Dto\CreateShortUrlDTO;
use App\Filament\User\Resources\ShortUrlResource;
use App\Services\ShortUrl\ShortUrlService;
use App\Services\Time\UserTimezoneService;
use App\Support\SlugGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $slug = $this->normalizeSlug((string) ($data['_slug'] ?? ''));
        $pengguna = auth()->user();
        $waktu = app(UserTimezoneService::class);

        $payload = new CreateShortUrlDTO(
            destinationUrl: (string) $data['destination_url'],
            customSlug: SlugGenerator::isCustomSlugValid($slug) ? $slug : null,
            isActive: (bool) ($data['is_active'] ?? true),
            startsAt: $waktu->keServer($data['starts_at'] ?? null, $pengguna),
            expiresAt: $waktu->keServer($data['expires_at'] ?? null, $pengguna),
            password: ($data['_protected'] ?? false) ? ($data['_password'] ?? null) : null,
        );

        $shortUrl = app(ShortUrlService::class)->create($pengguna, $payload);

        Notification::make()
            ->success()
            ->title(__('ui.panel.berhasil_dibuat'))
            ->body(__('ui.panel.slug_jadi', ['url' => $shortUrl->shortUrl()]))
            ->send();

        return $shortUrl;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function normalizeSlug(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $path = parse_url($value, PHP_URL_PATH);

        if ($path !== null && $path !== false && $path !== '') {
            $parts = array_values(array_filter(explode('/', $path), fn ($p) => $p !== ''));

            $value = (string) end($parts);
        }

        return trim($value, "/ \t\n\r\0\x0B");
    }
}
