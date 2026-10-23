<?php

namespace App\Services\Redirect;

use App\Dto\RequestContextDTO;
use App\Models\ShortUrl;
use App\Services\Analytics\AnalyticsService;
use App\Support\Enums\LinkAccessStatus;

/**
 * Aturan bisnis akses tautan pendek: mencari slug, menentukan keaktifan, dan
 * mencatat klik yang sesuai.
 *
 * Tidak mengetahui HTTP maupun tampilan. Pemanggil yang menerjemahkan hasilnya
 * menjadi respons, termasuk urusan sesi pengunjung dan konteks klik.
 */
class LinkAccessService
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly LinkPasswordService $passwords,
    ) {}

    public function find(string $slug): ?ShortUrl
    {
        return ShortUrl::query()->where('slug', $slug)->first();
    }

    /**
     * Tentukan hasil akses sebuah tautan sekaligus catat klik yang sesuai.
     *
     * @param  bool  $alreadyUnlocked  pengunjung sudah melewati kata sandi pada sesi ini
     */
    public function open(ShortUrl $shortUrl, bool $alreadyUnlocked, RequestContextDTO $context): LinkAccessStatus
    {
        return $this->logClick($shortUrl, $this->statusOf($shortUrl, $alreadyUnlocked), $context);
    }

    /**
     * Periksa kata sandi tautan, tetapi hanya bila tautannya memang bisa dibuka.
     */
    public function checkPassword(ShortUrl $shortUrl, string $password, RequestContextDTO $context): LinkAccessStatus
    {
        $status = $this->statusOf($shortUrl, alreadyUnlocked: true);

        if ($status !== LinkAccessStatus::Ready) {
            return $this->logClick($shortUrl, $status, $context);
        }

        return $this->logClick($shortUrl, $this->passwords->verify($shortUrl, $password), $context);
    }

    /**
     * Tentukan hasil akses dari keadaan data tautan, tanpa mencatat klik.
     */
    private function statusOf(ShortUrl $shortUrl, bool $alreadyUnlocked): LinkAccessStatus
    {
        return match (true) {
            ! $shortUrl->is_active => LinkAccessStatus::Inactive,
            $shortUrl->isExpired() => LinkAccessStatus::Expired,
            $shortUrl->notStarted() => LinkAccessStatus::Scheduled,
            $shortUrl->hasPassword() && ! $alreadyUnlocked => LinkAccessStatus::PasswordRequired,
            default => LinkAccessStatus::Ready,
        };
    }

    /**
     * Nama kunci sesi penanda bahwa pengunjung sudah membuka kunci tautan.
     */
    public static function sessionKey(string $slug): string
    {
        return 'shortlink_unlocked_'.$slug;
    }

    private function logClick(ShortUrl $shortUrl, LinkAccessStatus $status, RequestContextDTO $context): LinkAccessStatus
    {
        $clickStatus = $status->clickStatus();

        if ($clickStatus) {
            $this->analytics->recordClick($shortUrl, $clickStatus, $context);
        }

        return $status;
    }
}
