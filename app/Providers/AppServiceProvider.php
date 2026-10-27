<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\ShortUrl;
use App\Models\User;
use App\Observers\ShortUrlAuditObserver;
use App\Observers\UserAuditObserver;
use App\Services\Setting\OperationalSettingsService;
use App\View\Composers\BrandingComposer;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Keycloak\KeycloakExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Tampilan yang membutuhkan data branding siap pakai.
     * Setiap tampilan di daftar ini meng-include partials.tokens, sehingga token
     * warna pada partial tersebut ikut terisi dari data yang sama. Komponen
     * halaman-tautan perlu terdaftar sendiri karena komponen tidak mewarisi data
     * dari tampilan pemanggilnya.
     */
    private const TAMPILAN_BERBRANDING = [
        'publik.home',
        'publik.linktree',
        'auth.login',
        'auth.login-local',
        'auth.register',
        'auth.forgot-password',
        'auth.reset-password',
        'short.error',
        'short.not_found',
        'short.password',
        'components.halaman-tautan',
        'filament.user.layouts.panel',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa HTTPS hanya di luar request HTTP (CLI, queue, scheduler).
        //
        // Untuk request HTTP, skema asli sudah diteruskan nginx lewat
        // `X-Forwarded-Proto` (lihat `nginx/shortlink-pro.conf`), jadi Laravel
        // tahu sendiri kapan harus menghasilkan https. Memaksa https di sini
        // membuat akses HTTP yang sah — SSH tunnel `http://127.0.0.1:9000`
        // maupun mode `install.sh --listen-all` — meminta aset ke
        // `https://...` padahal origin tidak punya TLS, sehingga halaman
        // tampil tanpa CSS/JS.
        //
        // Konteks CLI/queue tetap dipaksa https karena URL di sana dibangun
        // dari `APP_URL` (mis. tautan di email dan QR code).
        if (str_starts_with(config('app.url', ''), 'https://') && $this->app->runningInConsole()) {
            URL::forceScheme('https');
        }

        $this->app->bind(
            LogoutResponseContract::class,
            LogoutResponse::class,
        );

        Event::listen(
            SocialiteWasCalled::class,
            KeycloakExtendSocialite::class.'@handle',
        );

        View::composer(self::TAMPILAN_BERBRANDING, BrandingComposer::class);

        View::composer(['publik.home', 'filament.user.layouts.panel'], function ($view): void {
            try {
                $view->with('pengumuman', app(OperationalSettingsService::class)->announcementBanner());
            } catch (\Throwable) {
                $view->with('pengumuman', null);
            }
        });

        RateLimiter::for('pembuatan-tautan', function (Request $request): Limit {
            return Limit::perMinute(app(OperationalSettingsService::class)->rateLimitPerMinute())
                ->by($request->ip());
        });

        User::observe(UserAuditObserver::class);
        ShortUrl::observe(ShortUrlAuditObserver::class);
    }
}
