<?php

namespace App\Support\Validation;

use App\Services\Setting\OperationalSettingsService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Menolak domain tujuan yang diblokir admin.
 *
 * Daftar dibaca dari pengaturan operasional (`blocked_domains`) sehingga
 * admin bisa memblokir phishing tanpa kunci API. Subdomain ikut diblokir:
 * `contoh.test` menolak `sub.contoh.test`. Pemeriksaan lokal murni, jadi
 * impor CSV tetap memakainya (tidak seperti Safe Browsing yang dilewati
 * saat impor massal).
 */
class DomainDiblokir implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $host = mb_strtolower((string) parse_url($value, PHP_URL_HOST));

        if ($host === '') {
            return;
        }

        $host = rtrim($host, '.');

        foreach (app(OperationalSettingsService::class)->blockedDomains() as $blokir) {
            if ($host === $blokir || str_ends_with($host, '.'.$blokir)) {
                Log::warning('Tautan tujuan memakai domain yang diblokir.', [
                    'host' => $host,
                ]);

                $fail('Domain tujuan "'.$host.'" diblokir admin sehingga tidak bisa dipendekkan.');

                return;
            }
        }
    }
}
