{{--
    Pemilih bahasa halaman publik.

    Tamu menyimpan pilihan di sesi miliknya; pengguna yang masuk menyimpannya di
    kolom `users.locale` miliknya sendiri (lihat App\Services\Setting\LocaleService),
    sehingga pilihan bahasa satu orang tidak memengaruhi orang lain.
--}}
@php($bahasaAktif = app()->getLocale())
<div class="pemilih-bahasa" role="group" aria-label="{{ __('ui.umum.pilih_bahasa') }}">
    @foreach (config('bahasa.tersedia', []) as $kode => $nama)
        <form method="POST" action="{{ route('bahasa.ganti') }}" class="pemilih-bahasa__form">
            @csrf
            <input type="hidden" name="locale" value="{{ $kode }}">
            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
            <button
                type="submit"
                class="pemilih-bahasa__tombol{{ $bahasaAktif === $kode ? ' pemilih-bahasa__tombol--aktif' : '' }}"
                title="{{ $nama }}"
                @if ($bahasaAktif === $kode) aria-current="true" @endif
            >{{ strtoupper($kode) }}</button>
        </form>
    @endforeach
</div>
