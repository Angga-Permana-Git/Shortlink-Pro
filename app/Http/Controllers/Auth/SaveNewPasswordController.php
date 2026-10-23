<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use App\Support\Enums\PasswordUpdateOutcome;
use Illuminate\Http\RedirectResponse;

/**
 * Menyimpan kata sandi baru dari tautan atur ulang.
 *
 * Pemeriksaan token dan peng-hash-an kata sandi ada di PasswordResetService;
 * di sini hanya penerjemahan hasilnya menjadi pesan dan respons.
 */
class SaveNewPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request, PasswordResetService $reset): RedirectResponse
    {
        $hasil = $reset->aturUlang($request->validated());

        if ($hasil !== PasswordUpdateOutcome::Updated) {
            return back()->withInput($request->only('email'))->withErrors(['email' => $this->pesan($hasil)]);
        }

        return redirect()->route('login.local')
            ->with('status', 'Kata sandi sudah diperbarui. Silakan masuk dengan kata sandi baru.');
    }

    private function pesan(PasswordUpdateOutcome $hasil): string
    {
        return match ($hasil) {
            PasswordUpdateOutcome::ExternalAccount => 'Akun ini bukan akun lokal, jadi kata sandinya tidak diatur di sini.',
            PasswordUpdateOutcome::Throttled => 'Tautan sebelumnya belum kedaluwarsa. Coba lagi sebentar lagi.',
            default => 'Tautan atur ulang tidak valid atau sudah kedaluwarsa.',
        };
    }
}
