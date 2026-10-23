<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menampilkan formulir kata sandi baru dari tautan atur ulang.
 *
 * Token dan email ikut dikirim kembali saat formulir disimpan; keabsahannya
 * diperiksa SaveNewPasswordController lewat broker kata sandi Laravel.
 */
class ShowResetPasswordController extends Controller
{
    public function __invoke(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }
}
