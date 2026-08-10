<?php

namespace App\Services\Analytics;

use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Support\Enums\ClickStatus;

class AnalyticsService
{
    public function recordClick(ShortUrl $shortUrl, ClickStatus $status): void
    {
        ClickEvent::create([
            'short_url_id' => $shortUrl->id,
            'status' => $status->value,
            'ip_hash' => $this->hashIp(request()->ip()),
            'user_agent' => mb_substr(request()->userAgent() ?? '', 0, 500) ?: null,
            'referer' => mb_substr(request()->headers->get('referer') ?? '', 0, 500) ?: null,
        ]);

        if ($status->redirects()) {
            $shortUrl->increment('total_clicks');
            $shortUrl->update(['last_clicked_at' => now()]);
        }
    }

    public function totalClicks(ShortUrl $shortUrl): int
    {
        return $shortUrl->clicks()->where('status', ClickStatus::Success->value)->count();
    }

    private function hashIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        return hash('sha256', $ip . config('app.key'));
    }
}