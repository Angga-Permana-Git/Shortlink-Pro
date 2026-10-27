<?php

namespace App\Http\Controllers;

use App\Dto\RegisterUserDTO;
use App\Http\Requests\RegisterUserRequest;
use App\Services\Auth\LandingService;
use App\Services\Auth\RegistrationService;
use App\Services\Setting\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, RegistrationService $registration, LandingService $landing, LocaleService $locale): RedirectResponse
    {
        $user = $registration->register(RegisterUserDTO::fromArray($request->validated()));

        Auth::login($user);

        // Pilihan bahasa di halaman daftar harus ikut tersimpan di akun baru,
        // agar tidak kembali ke bahasa bawaan setelah keluar dan masuk lagi.
        $locale->simpanDariSesi($user);

        return redirect($landing->for($user));
    }
}
