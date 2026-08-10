<?php

use App\Http\Controllers\Auth\KeycloakAuthController;
use App\Http\Controllers\Auth\LocalAuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'show']);
Route::get('/login', [LoginController::class, 'show'])->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/auth/keycloak/redirect', [KeycloakAuthController::class, 'redirect'])->name('keycloak.redirect');
    Route::get('/auth/keycloak/callback', [KeycloakAuthController::class, 'callback'])->name('keycloak.callback');
    Route::get('/login/local', [LocalAuthController::class, 'showLoginForm'])->name('login.local');
    Route::post('/login/local', [LocalAuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [KeycloakAuthController::class, 'logout'])->name('logout');
});

Route::post('{slug}', [RedirectController::class, 'unlock'])->name('redirect.unlock');
Route::get('{slug}', [RedirectController::class, 'show'])->name('redirect.show');
