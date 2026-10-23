<?php

namespace App\Http\Middleware;

use App\Services\Setting\OperationalSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Membatasi panel admin ke daftar IP/CIDR yang diizinkan bila diaktifkan. */
class RestrictAdminIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(OperationalSettingsService::class)->ipDiizinkan((string) $request->ip())) {
            abort(403, 'Akses admin dibatasi untuk alamat IP ini.');
        }

        return $next($request);
    }
}
