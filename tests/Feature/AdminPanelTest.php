<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use App\Services\Branding\BrandingService;
use App\Services\Setting\LocaleService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Panel admin: sidebar bisa disembunyikan, bahasa per pengguna, dan logo
 * branding dipakai bersama oleh panel admin maupun panel pengguna.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_can_be_hidden(): void
    {
        $this->assertTrue(Filament::getPanel('admin')->isSidebarFullyCollapsibleOnDesktop());
    }

    public function test_both_panels_apply_the_signed_in_user_locale(): void
    {
        foreach (['admin', 'user'] as $id) {
            $this->assertContains(
                SetLocale::class,
                Filament::getPanel($id)->getMiddleware(),
                "Panel {$id} harus menerapkan bahasa pengguna.",
            );
        }
    }

    public function test_locale_middleware_uses_the_user_choice(): void
    {
        $pengguna = User::factory()->create(['locale' => 'en']);

        $request = Request::create('/user');
        $request->setUserResolver(fn () => $pengguna);

        (new SetLocale(app(LocaleService::class)))->handle($request, fn () => response('ok'));

        $this->assertSame('en', app()->getLocale());
    }

    public function test_branding_logo_is_shared_by_admin_and_user_panels(): void
    {
        $logo = app(BrandingService::class)->loginLogoUrl();

        $this->assertSame($logo, Filament::getPanel('admin')->getBrandLogo());
        $this->assertSame($logo, Filament::getPanel('user')->getBrandLogo());
    }
}
