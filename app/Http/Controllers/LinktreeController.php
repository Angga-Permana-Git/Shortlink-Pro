<?php

namespace App\Http\Controllers;

use App\Services\Linktree\LinktreeAvatarService;
use App\Services\Linktree\LinktreeEventService;
use App\Services\Linktree\LinktreeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinktreeController extends Controller
{
    public function show(string $alamat, LinktreeService $linktree, LinktreeAvatarService $avatar, LinktreeEventService $events, Request $request): View
    {
        $profile = $linktree->publicProfile($alamat);
        $events->catatKunjungan($profile, $request);

        return view('publik.linktree', [
            'profile' => $profile,
            'logo' => $avatar->url($profile),
        ]);
    }
}
