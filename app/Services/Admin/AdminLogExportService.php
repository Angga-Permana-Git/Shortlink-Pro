<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\ClickEvent;
use App\Models\LoginAuditLog;
use App\Models\RoleChangeLog;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Menyusun isi CSV jejak admin. Daftar kolom tiap jenis log dijaga di satu
 * tempat agar unduhan dan tampilan tabel tidak berbeda jauh.
 */
class AdminLogExportService
{
    private const BATAS_BARIS = 10000;

    /**
     * @return array{judul: string, baris: array<int, array<int, string|int|null>>}
     */
    public function ekspor(string $jenis): array
    {
        return match ($jenis) {
            'audit' => $this->audit(),
            'login' => $this->login(),
            'klik' => $this->klik(),
            'peran' => $this->peran(),
            default => throw new InvalidArgumentException('Jenis log tidak dikenal.'),
        };
    }

    /** @return array{judul: string, baris: array<int, array<int, string|int|null>>} */
    private function audit(): array
    {
        $baris = [['ID', 'Waktu', 'Aktor', 'Aksi', 'Jenis Sumber', 'ID Sumber', 'Detail', 'Alamat IP']];

        foreach ($this->ambil(AuditLog::query()->with('actor')) as $log) {
            $baris[] = [
                $log->id,
                $log->created_at?->format('Y-m-d H:i:s'),
                $log->actor?->name ?? 'Sistem',
                $log->action,
                $log->resource_type,
                $log->resource_id,
                $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '',
                $log->ip_address,
            ];
        }

        return ['judul' => 'log-audit', 'baris' => $baris];
    }

    /** @return array{judul: string, baris: array<int, array<int, string|int|null>>} */
    private function login(): array
    {
        $baris = [['Waktu', 'Pengguna', 'Nama Pengguna', 'Penyedia', 'Status', 'Alamat IP', 'Perangkat']];

        foreach ($this->ambil(LoginAuditLog::query()->with('user')) as $log) {
            $baris[] = [
                $log->created_at?->format('Y-m-d H:i:s'),
                $log->user?->name ?? 'Tidak diketahui',
                $log->username,
                $log->auth_provider,
                $log->status,
                $log->ip_address,
                $log->user_agent,
            ];
        }

        return ['judul' => 'log-masuk', 'baris' => $baris];
    }

    /** @return array{judul: string, baris: array<int, array<int, string|int|null>>} */
    private function klik(): array
    {
        $baris = [['Waktu', 'Tautan Pendek', 'Status', 'IP (hash)', 'Perujuk', 'Perangkat']];

        foreach ($this->ambil(ClickEvent::query()->with('shortUrl')) as $klik) {
            $baris[] = [
                $klik->created_at?->format('Y-m-d H:i:s'),
                $klik->shortUrl?->slug,
                $klik->status,
                $klik->ip_hash,
                $klik->referer,
                $klik->user_agent,
            ];
        }

        return ['judul' => 'log-klik', 'baris' => $baris];
    }

    /** @return array{judul: string, baris: array<int, array<int, string|int|null>>} */
    private function peran(): array
    {
        $baris = [['Waktu', 'Pelaku', 'Target', 'Peran Lama', 'Peran Baru', 'Alamat IP']];

        foreach ($this->ambil(RoleChangeLog::query()->with(['actor', 'target'])) as $log) {
            $baris[] = [
                $log->created_at?->format('Y-m-d H:i:s'),
                $log->actor?->name ?? 'Sistem',
                $log->target?->name,
                $log->old_role,
                $log->new_role,
                $log->ip_address,
            ];
        }

        return ['judul' => 'log-peran', 'baris' => $baris];
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return iterable<int, TModel>
     */
    private function ambil(Builder $query): iterable
    {
        return $query->latest('created_at')->limit(self::BATAS_BARIS)->cursor();
    }
}
