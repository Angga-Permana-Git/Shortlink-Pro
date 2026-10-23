<?php

namespace App\Services\ShortUrl;

use App\Dto\CreateShortUrlDTO;
use App\Dto\UpdateShortUrlDTO;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Setting\OperationalSettingsService;
use App\Support\SlugGenerator;
use Illuminate\Support\Facades\Hash;

/**
 * Pembuatan, perubahan, dan penghapusan tautan pendek. Jejak audit dicatat
 * ShortUrlAuditObserver agar perubahan dari panel admin ikut tercatat.
 */
class ShortUrlService
{
    public function create(?User $owner, CreateShortUrlDTO $data): ShortUrl
    {
        $expiresAt = $data->expiresAt;
        $hariBawaan = app(OperationalSettingsService::class)->defaultExpiryDays();

        if ($expiresAt === null && $hariBawaan > 0) {
            $expiresAt = now()->addDays($hariBawaan);
        }

        $shortUrl = ShortUrl::create([
            'owner_id' => $owner?->id,
            'slug' => $data->customSlug ?: SlugGenerator::generate(),
            'destination_url' => $data->destinationUrl,
            'is_active' => $data->isActive,
            'starts_at' => $data->startsAt,
            'expires_at' => $expiresAt,
            'password_hash' => $data->password ? Hash::make($data->password) : null,
            'total_clicks' => 0,
        ]);

        return $shortUrl;
    }

    public function update(ShortUrl $shortUrl, User $actor, UpdateShortUrlDTO $data): ShortUrl
    {
        $shortUrl->update([
            'destination_url' => $data->destinationUrl,
            'is_active' => $data->isActive ?? $shortUrl->is_active,
            'starts_at' => $data->startsAt,
            'expires_at' => $data->expiresAt,
            'password_hash' => $this->resolvePasswordHash($shortUrl, $data),
        ]);

        return $shortUrl->refresh();
    }

    public function delete(ShortUrl $shortUrl, User $actor): void
    {
        $shortUrl->delete();
    }

    /**
     * Kata sandi baru bila pemanggil mengirimnya, dipertahankan bila tidak,
     * dan dilepas bila pemanggil menyatakan tautan tidak lagi dilindungi.
     */
    private function resolvePasswordHash(ShortUrl $shortUrl, UpdateShortUrlDTO $data): ?string
    {
        if ($data->protected === false) {
            return null;
        }

        return $data->password ? Hash::make($data->password) : $shortUrl->password_hash;
    }
}
