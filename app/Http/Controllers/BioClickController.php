<?php

namespace App\Http\Controllers;

use App\Models\BioLink;
use App\Services\Bio\BioEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BioClickController extends Controller
{
    public function __invoke(string $alamat, BioLink $tautan, BioEventService $events, Request $request): RedirectResponse
    {
        $profil = $tautan->profile;
        abort_unless($profil->is_active && $profil->public_slug === $alamat && $tautan->is_active, 404);

        $events->catatKlik($tautan, $request);

        return redirect()->away($tautan->url)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
