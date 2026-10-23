<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StopImpersonationController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::find($adminId);
        abort_unless($admin?->isAdmin(), 403);

        $targetId = $request->user()?->getKey();

        AuditLogger::log($admin, 'user.impersonate_stop', 'user', $targetId);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($admin);
        $request->session()->put('password_hash_web', $admin->getAuthPassword());

        return redirect('/admin/users');
    }
}
