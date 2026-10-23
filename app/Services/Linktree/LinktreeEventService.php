<?php

namespace App\Services\Linktree;

use App\Models\LinktreeEvent;
use App\Models\LinktreeLink;
use App\Models\LinktreeProfile;
use Illuminate\Http\Request;

/** Mencatat kunjungan halaman Linktree dan klik tautannya. */
class LinktreeEventService
{
    public function catatKunjungan(LinktreeProfile $profil, Request $request): void
    {
        $this->catat($profil, null, 'kunjungan', $request);
    }

    public function catatKlik(LinktreeLink $tautan, Request $request): void
    {
        $this->catat($tautan->profile, $tautan->id, 'klik', $request);
    }

    private function catat(LinktreeProfile $profil, ?int $idTautan, string $jenis, Request $request): void
    {
        LinktreeEvent::create([
            'linktree_profile_id' => $profil->id,
            'linktree_link_id' => $idTautan,
            'jenis' => $jenis,
            'ip_hash' => $this->hashIp($request->ip()),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
            'referer' => mb_substr((string) $request->headers->get('referer'), 0, 500) ?: null,
        ]);
    }

    private function hashIp(?string $ip): ?string
    {
        return $ip ? hash('sha256', $ip.config('app.key')) : null;
    }
}
