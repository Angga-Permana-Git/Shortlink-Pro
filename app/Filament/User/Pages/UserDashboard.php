<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Pages\Concerns\MembuatTautan;
use App\Filament\User\Pages\Concerns\MengelolaDaftarTautan;
use App\Filament\User\Pages\Concerns\MengelolaLinktree;
use App\Filament\User\Pages\Concerns\MengelolaTautan;
use App\Filament\User\Pages\Concerns\MengelolaZonaWaktu;
use App\Models\User;
use App\Services\Dashboard\UserDashboardService;
use App\Services\Time\UserTimezoneService;
use Filament\Pages\Page;
use Filament\Panel;
use Livewire\WithFileUploads;

/**
 * Halaman tunggal pengguna setelah masuk.
 *
 * Keempat kartu (dashboard, pemendek tautan, link saya, profil Linktree) berada
 * pada satu lembar, sehingga pengguna tidak perlu berpindah halaman. Halaman ini
 * memakai layout sendiri tanpa sidebar dan tidak didaftarkan di navigasi panel.
 *
 * Kelas ini hanya memuat keadaan bersama dan data tampilan; aksi tiap kartu ada
 * di trait pada Concerns supaya tiap berkas tetap di bawah batas ukuran
 * (disiplin_struktur.md bagian 2), dan seluruh penyimpanan didelegasikan ke
 * service di app/Services.
 */
class UserDashboard extends Page
{
    use MembuatTautan;
    use MengelolaDaftarTautan;
    use MengelolaLinktree;
    use MengelolaTautan;
    use MengelolaZonaWaktu;
    use WithFileUploads;

    protected static string $layout = 'filament.user.layouts.panel';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Dashboard Saya';

    public function getTitle(): string
    {
        return __('ui.umum.dashboard_saya');
    }

    protected string $view = 'filament.user.pages.user-dashboard';

    /** Rentang analitik Dashboard: 7, 30, atau 90 hari. */
    public int $periode = 30;

    public static function getRoutePath(Panel $panel): string
    {
        return '/';
    }

    public function mount(): void
    {
        $this->mountZonaWaktu();

        $layanan = app(UserDashboardService::class);
        $pengguna = auth()->user();
        $tinjauan = $layanan->tinjauanLinktree($pengguna);

        $this->pilihanLinktree = $this->idTautanTerpilih($layanan, $pengguna);
        $this->terbitkan = $tinjauan['terbit'];
        $this->profil['display_name'] = $tinjauan['nama'];
        $this->profil['bio'] = (string) $tinjauan['bio'];
        $this->profil['public_slug'] = (string) $tinjauan['slug'];
    }

    public function ubahPeriode(int $hari): void
    {
        $this->periode = in_array($hari, [7, 30, 90], true) ? $hari : 30;
    }

    public function terapkanFilter(string $filter): void
    {
        $this->filterStatus = $filter;
    }

    /**
     * Data segar untuk tampilan, dihitung ulang setiap render supaya kartu
     * link dan tinjauan Linktree selalu menampilkan keadaan terakhir.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $pengguna = auth()->user();
        $layanan = app(UserDashboardService::class);
        $waktu = app(UserTimezoneService::class);

        return [
            'pengguna' => $pengguna,
            'zonaWaktu' => $this->zonaWaktu,
            'zonaWaktuLabel' => $waktu->label($waktu->zona($pengguna)),
            'zonaSekarang' => $waktu->zonaSekarang($pengguna),
            'daftarZonaWaktu' => $waktu->daftar(),
            'domainPendek' => parse_url(config('app.url'), PHP_URL_HOST),
            'domainProfil' => parse_url(config('app.url'), PHP_URL_HOST).'/u',
            'ringkasan' => $layanan->ringkasan($pengguna),
            'trenKlik' => $layanan->trenKlik($pengguna, $this->periode),
            'aktivitas' => $layanan->aktivitas($pengguna, $this->periode),
            'kesehatan' => $layanan->kesehatan($pengguna),
            'linktreeStatistik' => $layanan->linktreeStatistik($pengguna, $this->periode),
            'tautanTerpopuler' => $layanan->tautanTerpopuler($pengguna),
            'daftarTautan' => $layanan->daftarTautan($pengguna, $this->pencarian, $this->filterStatus, $this->urutkan),
            'tinjauan' => $layanan->tinjauanLinktree($pengguna),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function idTautanTerpilih(UserDashboardService $layanan, User $pengguna): array
    {
        return collect($layanan->daftarTautan($pengguna))
            ->where('dipilih', true)
            ->pluck('id')
            ->all();
    }
}
