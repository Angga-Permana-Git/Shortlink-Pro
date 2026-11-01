<?php

namespace App\Services\Bio;

use App\Models\BioEvent;
use App\Models\BioLink;
use App\Models\BioProfile;
use Illuminate\Http\Request;

/** Mencatat kunjungan halaman Bio dan klik tautannya. */
class BioEventService
{
    public function catatKunjungan(BioProfile $profil, Request $request): void
    {
        $this->catat($profil, null, 'kunjungan', $request);
    }

    public function catatKlik(BioLink $tautan, Request $request): void
    {
        $this->catat($tautan->profile, $tautan->id, 'klik', $request);
    }

    private function catat(BioProfile $profil, ?int $idTautan, string $jenis, Request $request): void
    {
        BioEvent::create([
            'bio_profile_id' => $profil->id,
            'bio_link_id' => $idTautan,
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
