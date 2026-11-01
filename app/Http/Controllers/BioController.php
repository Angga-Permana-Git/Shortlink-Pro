<?php

namespace App\Http\Controllers;

use App\Services\Bio\BioAvatarService;
use App\Services\Bio\BioEventService;
use App\Services\Bio\BioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BioController extends Controller
{
    public function show(string $alamat, BioService $bio, BioAvatarService $avatar, BioEventService $events, Request $request): View
    {
        $profile = $bio->publicProfile($alamat);
        $events->catatKunjungan($profile, $request);

        return view('publik.bio', [
            'profile' => $profile,
            'logo' => $avatar->url($profile),
        ]);
    }
}
