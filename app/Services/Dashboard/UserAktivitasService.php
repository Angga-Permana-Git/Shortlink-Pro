<?php

namespace App\Services\Dashboard;

use App\Models\ClickEvent;
use App\Models\User;
use App\Services\Time\UserTimezoneService;

/** Sumber, waktu, kualitas, dan aktivitas klik terbaru pengguna. */
class UserAktivitasService
{
    public function __construct(private readonly UserTimezoneService $waktu) {}

    /**
     * @return array<int, array{label: string, jumlah: int, persen: float}>
     */
    public function sumberKlik(User $pengguna, int $hari = 30): array
    {
        $referer = $this->klik($pengguna, $hari)->pluck('referer');
        $total = $referer->count();
        $kelompok = $referer->map(fn (?string $url): string => $this->labelSumber($url))
            ->countBy()
            ->sortDesc()
            ->take(5);

        return $kelompok->map(fn (int $jumlah, string $label): array => [
            'label' => $label,
            'jumlah' => $jumlah,
            'persen' => $total > 0 ? round(($jumlah / $total) * 100, 1) : 0,
        ])->values()->all();
    }

    /**
     * @return array{jam: array<int, int>, puncak: string}
     */
    public function jamSibuk(User $pengguna, int $hari = 30): array
    {
        $zona = $this->waktu->zona($pengguna);
        $jam = array_fill(0, 24, 0);

        foreach ($this->klik($pengguna, $hari)->pluck('created_at') as $waktu) {
            $jam[(int) $waktu->copy()->timezone($zona)->format('G')]++;
        }

        $puncak = array_search(max($jam), $jam, true);

        return ['jam' => $jam, 'puncak' => max($jam) > 0 ? sprintf('%02d.00 %s', $puncak, $this->waktu->label($zona)) : 'Belum ada klik'];
    }

    /**
     * @return array{total: int, berhasil: int, gagal: int, persen: float, rincian: array<int, array{label: string, jumlah: int}>}
     */
    public function kualitas(User $pengguna, int $hari = 30): array
    {
        $jumlah = $this->klik($pengguna, $hari)->selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $total = (int) $jumlah->sum();
        $berhasil = (int) ($jumlah['success'] ?? 0);
        $label = ['wrong_password' => 'Kata sandi salah', 'expired' => 'Kedaluwarsa', 'inactive' => 'Nonaktif', 'scheduled' => 'Belum mulai'];

        return [
            'total' => $total,
            'berhasil' => $berhasil,
            'gagal' => $total - $berhasil,
            'persen' => $total > 0 ? round(($berhasil / $total) * 100, 1) : 0,
            'rincian' => $jumlah->map(fn (int $n, string $status): array => [
                'label' => $label[$status] ?? $status,
                'jumlah' => $n,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<int, array{slug: string, status: string, waktu: string}>
     */
    public function terbaru(User $pengguna, int $batas = 5): array
    {
        return ClickEvent::query()
            ->with('shortUrl:id,slug')
            ->whereHas('shortUrl', fn ($q) => $q->where('owner_id', $pengguna->id))
            ->latest('created_at')
            ->limit($batas)
            ->get()
            ->map(fn (ClickEvent $klik): array => [
                'slug' => $klik->shortUrl?->slug ?? '-',
                'status' => $klik->status === 'success' ? 'Berhasil' : 'Gagal',
                'waktu' => $this->waktu->format($klik->created_at, $pengguna),
            ])->all();
    }

    private function klik(User $pengguna, int $hari)
    {
        return ClickEvent::query()
            ->whereHas('shortUrl', fn ($q) => $q->where('owner_id', $pengguna->id))
            ->where('created_at', '>=', now()->subDays($hari));
    }

    private function labelSumber(?string $url): string
    {
        if (! $url) {
            return 'Langsung';
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./', '', $host) : 'Lainnya';
    }
}
