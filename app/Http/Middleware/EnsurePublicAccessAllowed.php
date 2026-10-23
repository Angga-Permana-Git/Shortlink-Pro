<?php

namespace App\Http\Middleware;

use App\Services\Setting\AccessModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Arahkan tamu ke Keycloak saat administrator mengaktifkan wajib SSO.
 */
class EnsurePublicAccessAllowed
{
    public function __construct(private readonly AccessModeService $accessMode) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->accessMode->forceSso() && ! $request->user()) {
            return redirect()->route('keycloak.redirect');
        }

        return $next($request);
    }
}
