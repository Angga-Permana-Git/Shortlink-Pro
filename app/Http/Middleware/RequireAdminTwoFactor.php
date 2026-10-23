<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isAdmin()
            && $user->hasTwoFactor()
            && ! $request->session()->get('admin_2fa_ok')
            && ! $request->routeIs('admin.2fa.*')) {
            return redirect()->route('admin.2fa.show');
        }

        return $next($request);
    }
}
