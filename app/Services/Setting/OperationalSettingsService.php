<?php

namespace App\Services\Setting;

use App\Models\Setting;
use App\Models\User;
use App\Services\Audit\AuditLogger;

/**
 * Pengaturan operasional yang memengaruhi pembuatan tautan, retensi log,
 * pembatasan laju, dan pengumuman. Semua nilai dibaca lewat kelas ini agar
 * formulir admin dan pemakai di lapisan lain tidak menyimpang.
 */
class OperationalSettingsService
{
    public function reservedSlugs(): array
    {
        $nilai = Setting::get('reserved_slugs', []);

        if (! is_array($nilai)) {
            $nilai = preg_split('/[\s,]+/', (string) $nilai) ?: [];
        }

        return collect($nilai)
            ->map(fn ($slug): string => mb_strtolower(trim((string) $slug)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function defaultExpiryDays(): int
    {
        return max(0, (int) Setting::get('default_expiry_days', 0));
    }

    public function logRetentionDays(): int
    {
        return max(1, (int) Setting::get('log_retention_days', 90));
    }

    public function rateLimitPerMinute(): int
    {
        return max(1, (int) Setting::get('rate_limit_per_minute', 10));
    }

    public function announcementBanner(): ?string
    {
        $teks = trim((string) Setting::get('announcement_banner', ''));

        return $teks === '' ? null : $teks;
    }

    /** @return array<int, string> */
    public function adminIpAllowlist(): array
    {
        $nilai = Setting::get('admin_ip_allowlist', []);

        if (! is_array($nilai)) {
            $nilai = preg_split('/[\s,]+/', (string) $nilai) ?: [];
        }

        return collect($nilai)
            ->map(fn ($ip): string => trim((string) $ip))
            ->filter(fn (string $ip): bool => $ip !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function ipDiizinkan(string $ip): bool
    {
        $daftar = $this->adminIpAllowlist();

        if ($daftar === []) {
            return true;
        }

        foreach ($daftar as $izin) {
            if (! str_contains($izin, '/')) {
                if ($izin === $ip) {
                    return true;
                }

                continue;
            }

            [$subnet, $bit] = explode('/', $izin, 2);

            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                && $this->dalamCidr($ip, $subnet, (int) $bit)) {
                return true;
            }
        }

        return false;
    }

    private function dalamCidr(string $ip, string $subnet, int $bit): bool
    {
        $bit = max(0, min(32, $bit));
        $mask = $bit === 0 ? 0 : (0xFFFFFFFF << (32 - $bit)) & 0xFFFFFFFF;

        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }

    /**
     * @return array<string, mixed>
     */
    public function formValues(): array
    {
        return [
            'reserved_slugs' => implode(PHP_EOL, $this->reservedSlugs()),
            'default_expiry_days' => $this->defaultExpiryDays(),
            'log_retention_days' => $this->logRetentionDays(),
            'rate_limit_per_minute' => $this->rateLimitPerMinute(),
            'announcement_banner' => $this->announcementBanner(),
            'admin_ip_allowlist' => implode(PHP_EOL, $this->adminIpAllowlist()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, User $actor): void
    {
        $reserved = collect(preg_split('/[\s,]+/', (string) ($data['reserved_slugs'] ?? '')) ?: [])
            ->map(fn ($slug): string => mb_strtolower(trim((string) $slug)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        Setting::set('reserved_slugs', $reserved);
        Setting::set('default_expiry_days', max(0, (int) ($data['default_expiry_days'] ?? 0)));
        Setting::set('log_retention_days', max(1, (int) ($data['log_retention_days'] ?? 90)));
        Setting::set('rate_limit_per_minute', max(1, (int) ($data['rate_limit_per_minute'] ?? 10)));

        $banner = trim((string) ($data['announcement_banner'] ?? ''));
        Setting::set('announcement_banner', $banner === '' ? null : $banner);

        $ip = collect(preg_split('/[\s,]+/', (string) ($data['admin_ip_allowlist'] ?? '')) ?: [])
            ->map(fn ($nilai): string => trim((string) $nilai))
            ->filter()
            ->unique()
            ->values()
            ->all();
        Setting::set('admin_ip_allowlist', $ip);

        AuditLogger::log($actor, 'settings.operational.update', 'setting', null, [
            'reserved_slugs' => count($reserved),
            'default_expiry_days' => max(0, (int) ($data['default_expiry_days'] ?? 0)),
            'log_retention_days' => max(1, (int) ($data['log_retention_days'] ?? 90)),
            'rate_limit_per_minute' => max(1, (int) ($data['rate_limit_per_minute'] ?? 10)),
            'admin_ip_allowlist' => count($ip),
        ]);
    }
}
