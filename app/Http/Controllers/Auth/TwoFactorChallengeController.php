<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\TwoFactorService;
use App\Services\Branding\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorChallengeController
{
    public function show(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasTwoFactor()) {
            return redirect('/admin');
        }

        return view('auth.two-factor', ['branding' => [
            'nama' => app(BrandingService::class)->appName(),
            'logo' => app(BrandingService::class)->loginLogoUrl(),
        ]]);
    }

    public function verify(Request $request, TwoFactorService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        /** @var User $user */
        $user = $request->user();

        if (! $user->hasTwoFactor() || ! $service->verify((string) $user->two_factor_secret, (string) $request->input('code'))) {
            AuditLogger::log($user, 'admin.2fa.failed', 'user', $user->id);

            return back()->withErrors(['code' => 'Kode dua faktor tidak valid.']);
        }

        $request->session()->put('admin_2fa_ok', true);
        $request->session()->regenerate();

        AuditLogger::log($user, 'admin.2fa.success', 'user', $user->id);

        return redirect()->intended('/admin');
    }
}
