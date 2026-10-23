<?php

use App\Http\Controllers\Api\V1\LinktreeApiController;
use App\Http\Controllers\Api\V1\PublicShortUrlApiController;
use App\Http\Middleware\EnsurePublicAccessAllowed;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/links', [PublicShortUrlApiController::class, 'store'])
        ->middleware(['throttle:pembuatan-tautan', EnsurePublicAccessAllowed::class]);
    Route::middleware('auth')->group(function (): void {
        Route::get('/linktree', [LinktreeApiController::class, 'show']);
        Route::put('/linktree', [LinktreeApiController::class, 'update']);
    });
});
