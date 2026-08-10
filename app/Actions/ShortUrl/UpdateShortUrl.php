<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;

class UpdateShortUrl
{
    public function handle(ShortUrl $shortUrl, array $data): ShortUrl
    {
        $shortUrl->update([
            'destination_url' => $data['destination_url'],
            'is_active' => $data['is_active'] ?? $shortUrl->is_active,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'password_hash' => array_key_exists('password_hash', $data)
                ? $data['password_hash']
                : $shortUrl->password_hash,
        ]);

        return $shortUrl->refresh();
    }
}