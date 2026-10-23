<?php

namespace App\Http\Middleware;

use App\Services\Setting\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Terapkan bahasa antarmuka milik pengguna (atau sesi tamu) pada request ini.
 *
 * Dijalankan setelah sesi tersedia sehingga `$request->user()` sudah bisa
 * diselesaikan dari sesi.
 */
class SetLocale
{
    public function __construct(private readonly LocaleService $locale) {}

    public function handle(Request $request, Closure $next): Response
    {
        $aktif = $this->locale->aktif(
            $request->user(),
            $request->hasSession() ? $request->session()->get('locale') : null,
        );

        app()->setLocale($aktif);
        $request->attributes->set('locale', $aktif);

        return $next($request);
    }
}
