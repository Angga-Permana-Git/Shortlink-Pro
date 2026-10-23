<?php

namespace App\Observers;

use App\Models\ShortUrl;
use App\Services\Audit\AuditLogger;

/**
 * Mencatat seluruh perubahan tautan pendek, termasuk yang dibuat lewat panel
 * admin yang tidak melewati ShortUrlService.
 */
class ShortUrlAuditObserver
{
    /**
     * Kolom yang layak diaudit. Perubahan total_clicks/last_clicked_at dari
     * kunjungan biasa sengaja diabaikan agar log tidak banjir.
     */
    private const DIPANTAU = [
        'owner_id',
        'slug',
        'destination_url',
        'is_active',
        'starts_at',
        'expires_at',
        'password_hash',
    ];

    public function created(ShortUrl $tautan): void
    {
        $this->catat($tautan, 'link.create', [
            'slug' => $tautan->slug,
            'destination' => $tautan->destination_url,
        ]);
    }

    public function updated(ShortUrl $tautan): void
    {
        $perubahan = [];

        foreach (self::DIPANTAU as $kolom) {
            if (! $tautan->wasChanged($kolom)) {
                continue;
            }

            $perubahan[$kolom] = $kolom === 'password_hash'
                ? 'diperbarui'
                : ['lama' => $tautan->getOriginal($kolom), 'baru' => $tautan->getAttribute($kolom)];
        }

        if ($perubahan === []) {
            return;
        }

        $this->catat($tautan, 'link.update', [
            'slug' => $tautan->slug,
            'perubahan' => $perubahan,
        ]);
    }

    public function deleted(ShortUrl $tautan): void
    {
        if ($tautan->isForceDeleting()) {
            return;
        }

        $this->catat($tautan, 'link.delete', ['slug' => $tautan->slug]);
    }

    public function restored(ShortUrl $tautan): void
    {
        $this->catat($tautan, 'link.restore', ['slug' => $tautan->slug]);
    }

    public function forceDeleted(ShortUrl $tautan): void
    {
        $this->catat($tautan, 'link.force_delete', ['slug' => $tautan->slug]);
    }

    private function catat(ShortUrl $tautan, string $aksi, array $metadata): void
    {
        AuditLogger::log(
            auth()->user() ?? $tautan->owner,
            $aksi,
            'short_url',
            $tautan->getKey(),
            $metadata,
        );
    }
}
