{{--
    Pemilih bahasa untuk bilah atas panel Filament.
    Memakai tombol bawaan Filament agar tampilannya konsisten dengan panel.
--}}
<div class="fi-topbar-item" style="display: flex; flex-direction: row; align-items: center; gap: 0.25rem;" role="group" aria-label="Pilih bahasa">
    @foreach (config('bahasa.tersedia', []) as $kode => $nama)
        <form method="POST" action="{{ route('bahasa.ganti') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $kode }}">
            <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
            <x-filament::button
                type="submit"
                size="xs"
                :color="app()->getLocale() === $kode ? 'primary' : 'gray'"
                :outlined="app()->getLocale() !== $kode"
                :aria-pressed="app()->getLocale() === $kode ? 'true' : 'false'"
                title="{{ $nama }}"
            >{{ strtoupper($kode) }}</x-filament::button>
        </form>
    @endforeach
</div>
