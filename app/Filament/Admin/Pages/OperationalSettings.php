<?php

namespace App\Filament\Admin\Pages;

use App\Services\Audit\AuditLogger;
use App\Services\Setting\LogPurgeService;
use App\Services\Setting\OperationalSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OperationalSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected string $view = 'filament.admin.pages.operational-settings';

    protected static ?string $title = 'Pengaturan Operasional';

    protected static ?string $navigationLabel = 'Pengaturan Operasional';

    public function getTitle(): string
    {
        return __('Pengaturan Operasional');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pengaturan Operasional');
    }

    public array $data = [];

    public function mount(): void
    {
        $this->refreshForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Textarea::make('reserved_slugs')
                    ->label(__('Nama pendek yang dicadangkan'))
                    ->rows(4)
                    ->helperText(__('Satu nama per baris atau pisahkan dengan koma. Nama ini tidak dapat dipakai pengguna.')),
                Textarea::make('blocked_domains')
                    ->label(__('Domain tujuan yang diblokir'))
                    ->rows(3)
                    ->helperText(__('Satu domain per baris (contoh phishing.test). Subdomain ikut diblokir. Tanpa kunci API pun tetap menolak.')),
                TextInput::make('default_expiry_days')
                    ->label(__('Kedaluwarsa bawaan (hari)'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(3650)
                    ->helperText(__('0 berarti tautan baru tidak memiliki kedaluwarsa bawaan.')),
                TextInput::make('log_retention_days')
                    ->label(__('Retensi log (hari)'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(3650)
                    ->helperText(__('Log audit, masuk, klik, dan perubahan peran yang lebih lama akan dibersihkan otomatis.')),
                TextInput::make('rate_limit_per_minute')
                    ->label(__('Batas pembuatan tautan per menit'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->helperText(__('Dihitung per alamat IP untuk pembuatan tautan publik dan API.')),
                Textarea::make('announcement_banner')
                    ->label(__('Pengumuman'))
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText(__('Tampil di halaman pengguna dan beranda publik. Kosongkan untuk menyembunyikan.')),
                Textarea::make('admin_ip_allowlist')
                    ->label(__('Daftar IP admin yang diizinkan'))
                    ->rows(3)
                    ->helperText(__('Satu IP atau CIDR per baris (contoh 10.0.0.1 atau 10.0.0.0/24). Kosongkan untuk mengizinkan semua alamat.')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        app(OperationalSettingsService::class)->save($this->form->getState(), auth()->user());

        $this->refreshForm();

        Notification::make()->success()->title(__('Pengaturan operasional tersimpan'))->send();
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('pratinjau_purge')
                ->label(__('Pratinjau Pembersihan'))
                ->icon(Heroicon::OutlinedEye)
                ->modalHeading(__('Pratinjau pembersihan log'))
                ->modalDescription(fn (): string => $this->ringkasanPurge())
                ->action(fn () => $this->tampilkanPurge()),
            Action::make('bersihkan_log')
                ->label(__('Bersihkan Log'))
                ->icon(Heroicon::OutlinedTrash)
                ->requiresConfirmation()
                ->modalHeading(__('Bersihkan log lama?'))
                ->modalDescription(fn (): string => $this->ringkasanPurge())
                ->action(fn () => $this->bersihkanLog()),
        ];
    }

    private function ringkasanPurge(): string
    {
        $hasil = app(LogPurgeService::class)->pratinjau();

        return __(':total log lebih lama dari :hari hari (audit :audit, masuk :masuk, klik :klik, peran :peran) akan dihapus.', $hasil);
    }

    private function tampilkanPurge(): void
    {
        Notification::make()->success()->title(__('Pratinjau pembersihan'))->body($this->ringkasanPurge())->send();
    }

    private function bersihkanLog(): void
    {
        $hasil = app(LogPurgeService::class)->bersihkan();

        AuditLogger::log(auth()->user(), 'settings.logs.purge', 'setting', null, [
            'hari' => $hasil['hari'],
            'audit' => $hasil['audit'],
            'masuk' => $hasil['masuk'],
            'klik' => $hasil['klik'],
            'peran' => $hasil['peran'],
        ]);

        Notification::make()
            ->success()
            ->title(__('Pembersihan selesai'))
            ->body(__('Audit :audit, masuk :masuk, klik :klik, dan peran :peran dihapus.', $hasil))
            ->send();
    }

    private function refreshForm(): void
    {
        $this->form->fill(app(OperationalSettingsService::class)->formValues());
    }
}
