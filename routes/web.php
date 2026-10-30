<?php

use App\Http\Controllers\Admin\AdminLogExportController;
use App\Http\Controllers\Admin\StopImpersonationController;
use App\Http\Controllers\Auth\AuthenticateLocalController;
use App\Http\Controllers\Auth\HandleGoogleCallbackController;
use App\Http\Controllers\Auth\HandleKeycloakCallbackController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RedirectToGoogleController;
use App\Http\Controllers\Auth\RedirectToKeycloakController;
use App\Http\Controllers\Auth\SaveNewPasswordController;
use App\Http\Controllers\Auth\SendPasswordResetLinkController;
use App\Http\Controllers\Auth\ShowForgotPasswordController;
use App\Http\Controllers\Auth\ShowLocalLoginController;
use App\Http\Controllers\Auth\ShowResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\LinkExportController;
use App\Http\Controllers\LinktreeClickController;
use App\Http\Controllers\LinktreeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PublicShortUrlController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\Redirect\ShowShortUrlController;
use App\Http\Controllers\Redirect\UnlockShortUrlController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsurePublicAccessAllowed;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'show'])->middleware(EnsurePublicAccessAllowed::class);
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response()->view('robots')->header('Content-Type', 'text/plain; charset=utf-8'));
Route::post('/shorten', [PublicShortUrlController::class, 'store'])
    ->middleware(['throttle:pembuatan-tautan', EnsurePublicAccessAllowed::class])
    ->name('shorten.public');
// Alamat publik Linktree memakai slug pilihan pemiliknya, bukan username login.
Route::get('/u/{alamat}', [LinktreeController::class, 'show'])->name('linktree.show');
Route::get('/u/{alamat}/klik/{tautan}', LinktreeClickController::class)
    ->middleware('throttle:120,1')
    ->name('linktree.click');
Route::get('/qr/{slug}', [QrCodeController::class, 'show'])->name('qr.show');
// Pilihan bahasa tamu disimpan di sesinya sendiri; pengguna masuk di kolom
// `users.locale` miliknya (lihat LocaleService).
Route::post('/bahasa', LocaleController::class)->middleware('throttle:30,1')->name('bahasa.ganti');

Route::middleware('guest')->group(function () {
    Route::get('/auth/keycloak/redirect', RedirectToKeycloakController::class)->name('keycloak.redirect');
    Route::get('/auth/keycloak/callback', HandleKeycloakCallbackController::class)->name('keycloak.callback');
    Route::get('/auth/google/redirect', RedirectToGoogleController::class)->name('google.redirect');
    Route::get('/auth/google/callback', HandleGoogleCallbackController::class)->name('google.callback');
    Route::get('/login/local', ShowLocalLoginController::class)->name('login.local');
    Route::post('/login/local', AuthenticateLocalController::class)->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/forgot-password', ShowForgotPasswordController::class)->name('password.request');
    Route::post('/forgot-password', SendPasswordResetLinkController::class)->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', ShowResetPasswordController::class)->name('password.reset');
    Route::post('/reset-password', SaveNewPasswordController::class)->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/user/export/csv', LinkExportController::class)->name('user.export.csv');
    Route::get('/admin/ekspor/{jenis}', AdminLogExportController::class)
        ->middleware(EnsureUserIsAdmin::class)
        ->name('admin.export');
    Route::get('/admin/dua-faktor', [TwoFactorChallengeController::class, 'show'])
        ->middleware(EnsureUserIsAdmin::class)
        ->name('admin.2fa.show');
    Route::post('/admin/dua-faktor', [TwoFactorChallengeController::class, 'verify'])
        ->middleware([EnsureUserIsAdmin::class, 'throttle:6,1'])
        ->name('admin.2fa.verify');
    Route::post('/admin/impersonate/stop', StopImpersonationController::class)
        ->name('admin.impersonate.stop');
});

Route::post('{slug}', UnlockShortUrlController::class)->name('redirect.unlock');
Route::get('{slug}', ShowShortUrlController::class)->name('redirect.show');
