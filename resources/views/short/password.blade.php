<x-halaman-tautan
    :judul="__('ui.tautan.dilindungi_judul')"
    :pesan="__('ui.tautan.dilindungi_pesan')"
    nama-ikon="kunci">
    <form class="tautan__form" method="POST" action="{{ route('redirect.unlock', $shortUrl->slug) }}">
        @csrf
        <label class="tautan__label" for="password">{{ __('ui.tautan.label_sandi') }}</label>
        <input class="tautan__kontrol" id="password" type="password" name="password" required autofocus autocomplete="off">
        @error('password')
            <p class="tautan__galat" role="alert">{{ $message }}</p>
        @enderror
        <button class="tautan__aksi" type="submit">{{ __('ui.tautan.tombol_buka') }}</button>
    </form>
</x-halaman-tautan>
