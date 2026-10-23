<?php

namespace App\Services\Admin;

use App\Dto\ResetLinkResultDTO;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSupportService
{
    public function kirimTautanReset(User $target, User $actor): ResetLinkResultDTO
    {
        $hasil = app(PasswordResetService::class)->mintaTautan($target->email);

        AuditLogger::log($actor, 'user.reset_link', 'user', $target->id, [
            'hasil' => $hasil->outcome->value,
        ]);

        return $hasil;
    }

    public function keluarkanSesi(User $target, User $actor): int
    {
        $jumlah = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $target->getKey())
            ->delete();

        $target->forceFill(['remember_token' => Str::random(60)])->save();

        AuditLogger::log($actor, 'user.force_logout', 'user', $target->id, [
            'sesi_dihapus' => $jumlah,
        ]);

        return $jumlah;
    }

    public function mulaiImpersonasi(User $target, User $actor): RedirectResponse
    {
        abort_if($target->is($actor) || $target->isProtected() || ! $target->isActive(), 403);

        AuditLogger::log($actor, 'user.impersonate', 'user', $target->id);

        session()->put('impersonator_id', $actor->id);
        session()->put('impersonator_name', $actor->name);

        Auth::login($target);
        session()->regenerate();
        session()->put('password_hash_web', $target->getAuthPassword());

        return redirect('/user');
    }
}
