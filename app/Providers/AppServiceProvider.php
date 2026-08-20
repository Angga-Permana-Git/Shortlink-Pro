<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
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
        \Illuminate\Support\Facades\URL::forceScheme('https');

        \App\Services\System\TimezoneService::apply();

        $this->app->bind(
            LogoutResponseContract::class,
            \App\Http\Responses\LogoutResponse::class,
        );

        \Illuminate\Support\Facades\Event::listen(
            SocialiteWasCalled::class,
            \SocialiteProviders\Keycloak\KeycloakExtendSocialite::class.'@handle',
        );
    }
}
