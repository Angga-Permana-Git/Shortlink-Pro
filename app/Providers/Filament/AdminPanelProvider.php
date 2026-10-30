<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequireAdminTwoFactor;
use App\Http\Middleware\RestrictAdminIp;
use App\Http\Middleware\SetLocale;
use App\Services\Branding\BrandingService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn () => app(BrandingService::class)->appName())
            ->brandLogo(fn () => $this->brandLogo())
            ->brandLogoHeight('2.5rem')
            ->colors([
                'primary' => Color::Indigo,
            ])
            // Tombol lipat di bilah atas untuk menyembunyikan sidebar admin.
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('partials.pemilih-bahasa-filament'),
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.components.server-clock'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.widgets.partials.dashboard-css'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<meta name="robots" content="noindex, nofollow">',
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RestrictAdminIp::class,
                EnsureUserIsAdmin::class,
                RequireAdminTwoFactor::class,
            ]);
    }

    private function brandLogo(): ?string
    {
        return app(BrandingService::class)->loginLogoUrl();
    }
}
