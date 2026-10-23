<?php

namespace App\Http\Controllers;

use App\Dto\RegisterUserDTO;
use App\Http\Requests\RegisterUserRequest;
use App\Services\Auth\LandingService;
use App\Services\Auth\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, RegistrationService $registration, LandingService $landing): RedirectResponse
    {
        $user = $registration->register(RegisterUserDTO::fromArray($request->validated()));

        Auth::login($user);

        return redirect($landing->for($user));
    }
}
