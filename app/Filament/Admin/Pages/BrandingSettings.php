<?php

namespace App\Filament\Admin\Pages;

use App\Dto\UpdateBrandingDTO;
use App\Services\Branding\BrandingService;
use App\Services\Setting\AccessModeService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BrandingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected string $view = 'filament.admin.pages.branding-settings';

    protected static ?string $title = 'Branding';

    public function getTitle(): string
    {
        return __('Branding');
    }

    public static function getNavigationLabel(): string
    {
        return __('Branding');
    }

    public array $data = [];

    public ?string $currentLogoUrl = null;

    public ?string $currentLogoPath = null;

    public function mount(): void
    {
        $this->refreshForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('app_name')
                    ->label(__('Nama aplikasi'))
                    ->required()
                    ->placeholder(__('cth: Shortlink Pro'))
                    ->maxLength(255),
                Toggle::make('force_sso')
                    ->label(__('Paksa login Keycloak'))
                    ->helperText(__('Jika aktif, tamu diarahkan ke Keycloak sebelum melihat halaman publik.')),
                FileUpload::make('login_logo')
                    ->label(__('Unggah Logo Baru (PNG/JPG/SVG)'))
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->directory('branding')
                    ->disk('public')
                    ->visibility('public')
                    ->helperText(__('Ditampilkan di halaman login dan header. Format PNG, JPG, atau SVG. Maksimal 2MB.')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        app(BrandingService::class)->updateBranding(auth()->user(), UpdateBrandingDTO::fromArray([
            'app_name' => $data['app_name'] ?? null,
            'login_logo' => $data['login_logo'] ?? null,
        ]));

        app(AccessModeService::class)->setForceSso(auth()->user(), (bool) ($data['force_sso'] ?? false));

        $this->refreshForm();

        Notification::make()->success()->title(__('Pengaturan tersimpan'))->send();
    }

    public function deleteLogo(): void
    {
        app(BrandingService::class)->deleteLogo(auth()->user());

        $this->refreshForm();

        Notification::make()->success()->title(__('Logo berhasil dihapus'))->send();
    }

    /**
     * Muat ulang form dan pratinjau logo dari pengaturan yang tersimpan.
     *
     * Tampilan hanya membaca properti ini, bukan memanggil service sendiri.
     */
    private function refreshForm(): void
    {
        $branding = app(BrandingService::class);

        $this->currentLogoUrl = $branding->loginLogoUrl();
        $this->currentLogoPath = $branding->loginLogoPath();

        $this->form->fill([
            'app_name' => $branding->appName(),
            'force_sso' => app(AccessModeService::class)->forceSso(),
            'login_logo' => null,
        ]);
    }
}
