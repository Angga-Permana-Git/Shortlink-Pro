<?php

namespace App\Http\Controllers\Auth;

use App\Dto\ResetLinkResultDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Services\Auth\PasswordResetService;
use App\Support\Enums\ResetLinkOutcome;
use Illuminate\Http\RedirectResponse;

/**
 * Mengirim tautan atur ulang kata sandi untuk akun lokal.
 *
 * Pemeriksaan akun dan pengiriman email ada di PasswordResetService; di sini
 * hanya penerjemahan hasilnya menjadi pesan dan respons.
 */
class SendPasswordResetLinkController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, PasswordResetService $reset): RedirectResponse
    {
        $hasil = $reset->mintaTautan((string) $request->validated('email'));

        if ($hasil->outcome !== ResetLinkOutcome::Sent) {
            return back()->withInput()->withErrors(['email' => $this->pesan($hasil)]);
        }

        $redirect = back()->with('status', 'Tautan atur ulang sudah dikirim ke email akun Anda.');

        return $hasil->previewUrl
            ? $redirect->with('tautan_reset', $hasil->previewUrl)
            : $redirect;
    }

    private function pesan(ResetLinkResultDTO $hasil): string
    {
        return match ($hasil->outcome) {
            ResetLinkOutcome::ExternalAccount => $hasil->provider === 'google'
                ? 'Akun ini masuk lewat Google, jadi kata sandinya diatur di akun Google Anda.'
                : 'Akun ini memakai SSO kantor. Hubungi administrator bila kata sandi Anda bermasalah.',
            ResetLinkOutcome::Throttled => 'Tautan sebelumnya belum kedaluwarsa. Coba lagi sebentar lagi.',
            default => 'Email tidak dikenali atau akun tidak aktif.',
        };
    }
}
