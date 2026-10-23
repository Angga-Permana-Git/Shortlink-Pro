<?php

namespace App\Http\Controllers;

use App\Services\Setting\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Ganti bahasa antarmuka untuk satu peramban.
 *
 * Tamu: pilihan disimpan di sesinya sendiri. Pengguna masuk: pilihan disimpan
 * di kolom `users.locale` miliknya, sehingga tidak memengaruhi pengguna lain.
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, LocaleService $locale): RedirectResponse
    {
        $tersedia = array_keys($locale->tersedia());

        $data = $request->validate([
            'locale' => ['required', 'string', Rule::in($tersedia)],
        ]);

        $request->session()->put('locale', $data['locale']);

        if ($pengguna = $request->user()) {
            $locale->simpan($pengguna, $data['locale']);
        }

        // Kembali ke halaman yang mengirim formulir. Nilai dari formulir dipakai
        // lebih dulu agar bahasa tetap berpindah walau peramban tidak mengirim
        // header Referer. Hanya jalur lokal yang diterima.
        $tujuan = (string) $request->input('redirect', '');
        $tujuan = $tujuan !== '' && str_starts_with($tujuan, '/') && ! str_starts_with($tujuan, '//')
            ? $tujuan
            : url()->previous();

        return redirect()->to($tujuan);
    }
}
