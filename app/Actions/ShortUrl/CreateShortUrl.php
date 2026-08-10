<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use App\Support\SlugGenerator;

class CreateShortUrl
{
    public function handle(array $data, ?string $customSlug = null): ShortUrl
    {
        return ShortUrl::create([
            'owner_id' => $data['owner_id'],
            'slug' => $customSlug ?: SlugGenerator::generate(),
            'destination_url' => $data['destination_url'],
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'password_hash' => $data['password_hash'] ?? null,
            'total_clicks' => 0,
        ]);
    }
}