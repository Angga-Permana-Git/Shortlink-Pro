<?php

namespace App\Filament\User\Resources\ShortUrlResource\Pages;

use App\Filament\User\Resources\ShortUrlResource;
use App\Services\ShortUrl\ShortUrlService;
use App\Support\SlugGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $raw = (string) ($data['_slug'] ?? '');
        $slug = $this->normalizeSlug($raw);

        $customSlug = SlugGenerator::isCustomSlugValid($slug)
            ? $slug
            : null;

        $service = app(ShortUrlService::class);

        $shortUrl = $service->create(auth()->user(), [
            'destination_url' => $data['destination_url'],
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'password' => ($data['_protected'] ?? false) ? ($data['_password'] ?? null) : null,
        ], $customSlug);

        Notification::make()
            ->success()
            ->title('Link created')
            ->body('Short URL: '.$shortUrl->shortUrl())
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