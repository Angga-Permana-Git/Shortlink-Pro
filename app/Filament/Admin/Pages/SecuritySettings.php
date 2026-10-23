<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\TwoFactorService;
use App\Services\QrCode\QrCodeService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SecuritySettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.admin.pages.security-settings';

    protected static ?string $title = 'Keamanan Akun';

    protected static ?string $navigationLabel = 'Keamanan Akun';

    public function getTitle(): string
    {
        return __('Keamanan Akun');
    }

    public static function getNavigationLabel(): string
    {
        return __('Keamanan Akun');
    }

    public bool $aktif = false;

    public ?string $secret = null;

    public ?string $qrSvg = null;

    public string $kode = '';

    public function mount(): void
    {
        $this->aktif = $this->pengguna()->hasTwoFactor();
    }

    public function mulai(): void
    {
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        session(['admin_2fa_setup' => $secret]);

        $this->secret = $secret;
        $this->qrSvg = app(QrCodeService::class)->svg($service->otpauthUri($this->pengguna(), $secret));
        $this->kode = '';
    }

    public function konfirmasi(): void
    {
        $this->validate(['kode' => ['required', 'digits:6']]);

        if (! app(TwoFactorService::class)->aktifkan($this->pengguna(), $this->kode)) {
            $this->addError('kode', __('Kode tidak cocok. Periksa jam perangkat dan coba lagi.'));

            return;
        }

        $this->aktif = true;
        $this->secret = null;
        $this->qrSvg = null;
        $this->kode = '';

        AuditLogger::log($this->pengguna(), 'admin.2fa.enable', 'user', $this->pengguna()->id);

        Notification::make()->success()->title(__('Dua faktor aktif'))->send();
    }

    public function nonaktifkan(): void
    {
        app(TwoFactorService::class)->nonaktifkan($this->pengguna());
        session()->forget('admin_2fa_setup');

        $this->aktif = false;
        $this->secret = null;
        $this->qrSvg = null;

        AuditLogger::log($this->pengguna(), 'admin.2fa.disable', 'user', $this->pengguna()->id);

        Notification::make()->success()->title(__('Dua faktor dinonaktifkan'))->send();
    }

    public function batal(): void
    {
        session()->forget('admin_2fa_setup');

        $this->secret = null;
        $this->qrSvg = null;
        $this->kode = '';
    }

    private function pengguna(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
