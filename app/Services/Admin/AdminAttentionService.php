<?php

namespace App\Services\Admin;

use App\Filament\Admin\Resources\ClickEventResource;
use App\Filament\Admin\Resources\LinktreeProfileResource;
use App\Filament\Admin\Resources\LoginAuditLogResource;
use App\Filament\Admin\Resources\ShortUrlResource;
use App\Filament\Admin\Resources\UserResource;
use App\Models\ClickEvent;
use App\Models\LinktreeProfile;
use App\Models\LoginAuditLog;
use App\Models\ShortUrl;
use App\Models\User;

/** Menyiapkan peringatan operasional yang perlu dilihat admin. */
class AdminAttentionService
{
    /**
     * @return array<int, array{judul: string, pesan: string, jumlah: int, url: string}>
     */
    public function peringatan(): array
    {
        $tanpaAlamat = LinktreeProfile::where('is_active', true)->whereNull('public_slug')->count();
        $kedaluwarsa = ShortUrl::where('is_active', true)->whereBetween('expires_at', [now(), now()->addDays(7)])->count();
        $salahSandi = ClickEvent::where('status', 'wrong_password')->where('created_at', '>=', now()->subDay())->count();
        $tanpa2fa = User::where('role', 'admin')->where('is_active', true)->whereNull('two_factor_confirmed_at')->count();
        $baruTanpaTautan = User::where('is_active', true)->where('created_at', '<=', now()->subDays(7))->whereDoesntHave('shortUrls')->count();
        $nonaktifDenganTautan = User::where('is_active', false)->whereHas('shortUrls', fn ($q) => $q->where('is_active', true))->count();
        $loginGagal = LoginAuditLog::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count();
        $linktreeKosong = LinktreeProfile::where('is_active', true)->whereDoesntHave('links')->count();
        $klikGagal = ShortUrl::whereHas('clicks', fn ($q) => $q->where('status', 'wrong_password'))->count();
        $sistem = app(AdminSystemService::class)->ringkasan();

        return array_values(array_filter([
            $this->item('Profil Linktree tanpa alamat', $tanpaAlamat, 'profil belum memiliki alamat publik.', LinktreeProfileResource::getUrl('index')),
            $this->item('Tautan segera kedaluwarsa', $kedaluwarsa, 'tautan akan kedaluwarsa dalam 7 hari.', ShortUrlResource::getUrl('index')),
            $this->item('Percobaan kata sandi salah', $salahSandi, 'percobaan gagal dalam 24 jam terakhir.', ClickEventResource::getUrl('index')),
            $this->item('Admin tanpa 2FA', $tanpa2fa, 'akun admin aktif belum mengaktifkan dua faktor.', UserResource::getUrl('index')),
            $this->item('Pengguna baru belum membuat tautan', $baruTanpaTautan, 'akun aktif lebih dari 7 hari tanpa tautan.', UserResource::getUrl('index')),
            $this->item('Pengguna nonaktif punya tautan aktif', $nonaktifDenganTautan, 'tautan masih dapat diakses publik.', UserResource::getUrl('index')),
            $this->item('Login gagal berulang', $loginGagal, 'percobaan login gagal dalam 24 jam terakhir.', LoginAuditLogResource::getUrl('index')),
            $this->item('Linktree terbit tanpa tautan', $linktreeKosong, 'halaman publik belum memiliki tombol tautan.', LinktreeProfileResource::getUrl('index')),
            $this->item('Tautan dengan klik gagal', $klikGagal, 'tautan pernah menerima kata sandi salah.', ShortUrlResource::getUrl('index')),
            $this->item('Penjadwal belum berjalan', $sistem['jadwal_aktif'] ? 0 : 1, 'denyut schedule:run belum terdeteksi.', url('/admin')),
            $this->item('Antrean gagal', $sistem['antrean_gagal'], 'tugas antrean perlu diperiksa.', url('/admin')),
        ]));
    }

    /** @return array<int, string> */
    public function daftar(): array
    {
        return array_column($this->peringatan(), 'pesan');
    }

    /** @return array{judul: string, pesan: string, jumlah: int, url: string}|null */
    private function item(string $judul, int $jumlah, string $pesan, string $url): ?array
    {
        if ($jumlah === 0) {
            return null;
        }

        return ['judul' => __($judul), 'pesan' => __(':jumlah '.$pesan, ['jumlah' => $jumlah]), 'jumlah' => $jumlah, 'url' => $url];
    }
}
