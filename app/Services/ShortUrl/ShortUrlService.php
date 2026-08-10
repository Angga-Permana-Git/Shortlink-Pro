<?php

namespace App\Services\ShortUrl;

use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;

class ShortUrlService
{
    public function create(User $owner, array $data, ?string $customSlug = null): ShortUrl
    {
        $shortUrl = ShortUrl::create([
            'owner_id' => $owner->id,
            'slug' => $customSlug ?: \App\Support\SlugGenerator::generate(),
            'destination_url' => $data['destination_url'],
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'password_hash' => $this->resolvePasswordHash($data),
            'total_clicks' => 0,
        ]);

        AuditLogger::log($owner, 'link.create', 'short_url', $shortUrl->id, [
            'slug' => $shortUrl->slug,
            'destination' => $shortUrl->destination_url,
        ]);

        return $shortUrl;
    }

    public function update(ShortUrl $shortUrl, User $actor, array $data): ShortUrl
    {
        $shortUrl->update([
            'destination_url' => $data['destination_url'],
            'is_active' => $data['is_active'] ?? $shortUrl->is_active,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'password_hash' => $this->resolveNewPasswordHash($shortUrl, $data),
        ]);

        AuditLogger::log($actor, 'update', 'short_url', $shortUrl->id, [
            'slug' => $shortUrl->slug,
        ]);

        return $shortUrl->refresh();
    }

    public function delete(ShortUrl $shortUrl, User $actor): void
    {
        $slug = $shortUrl->slug;

        $shortUrl->delete();

        AuditLogger::log($actor, 'delete', 'short_url', $shortUrl->id, [
            'slug' => $slug,
        ]);
    }

    public function stringToCustomSlug(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        if (! \App\Support\SlugGenerator::isCustomSlugValid($slug)) {
            throw new \InvalidArgumentException('Custom slug contains invalid characters.');
        }

        return $slug;
    }

    private function resolveNewPasswordHash(ShortUrl $shortUrl, array $data): ?string
    {
        if (isset($data['protected']) && ! $data['protected']) {
            return null;
        }

        if (! empty($data['password'])) {
            return Hash::make($data['password']);
        }

        return $shortUrl->password_hash;
    }

    private function resolvePasswordHash(array $data): ?string
    {
        if (empty($data['password'])) {
            return null;
        }

        return Hash::make($data['password']);
    }
}