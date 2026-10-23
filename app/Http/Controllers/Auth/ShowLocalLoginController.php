<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Menampilkan formulir login darurat untuk akun lokal.
 */
class ShowLocalLoginController extends Controller
{
    public function __invoke(LandingService $landing): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->to($landing->for(auth()->user()));
        }

        return view('auth.login-local');
    }
}
