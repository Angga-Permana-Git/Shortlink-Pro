<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Menampilkan formulir permintaan tautan atur ulang kata sandi.
 */
class ShowForgotPasswordController extends Controller
{
    public function __invoke(): View
    {
        return view('auth.forgot-password');
    }
}
