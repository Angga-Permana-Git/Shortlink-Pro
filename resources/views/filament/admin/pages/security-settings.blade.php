<x-filament-panels::page>
    <div class="fi-section">
        <div class="fi-section-content p-6 space-y-4">
            <h2 class="text-lg font-semibold">{{ __('Autentikasi Dua Faktor') }}</h2>

            @if ($aktif)
                <p>{{ __('Dua faktor aktif. Setiap kali masuk, Anda perlu memasukkan kode dari aplikasi autentikator.') }}</p>

                <x-filament::button color="danger" wire:click="nonaktifkan" wire:confirm="{{ __('Nonaktifkan dua faktor untuk akun ini?') }}">
                    {{ __('Nonaktifkan Dua Faktor') }}
                </x-filament::button>
            @elseif ($secret)
                <p>{{ __('Pindai kode QR berikut dengan aplikasi autentikator, lalu masukkan kode 6 angka.') }}</p>

                <div class="max-w-xs rounded-lg bg-white p-4">{!! $qrSvg !!}</div>

                <p class="text-sm">{{ __('Atau masukkan kunci ini secara manual:') }} <code class="font-mono">{{ $secret }}</code></p>

                <form wire:submit="konfirmasi" class="space-y-3">
                    <div>
                        <label class="fi-fo-field-wrp-label" for="kode">{{ __('Kode 6 angka') }}</label>
                        <input id="kode" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                               class="fi-input block w-full" wire:model="kode" required>
                        @error('kode')
                            <p class="text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <x-filament::button type="submit" color="primary">{{ __('Aktifkan') }}</x-filament::button>
                        <x-filament::button color="gray" wire:click="batal">{{ __('Batal') }}</x-filament::button>
                    </div>
                </form>
            @else
                <p>{{ __('Tambahkan lapisan keamanan untuk akun admin dengan aplikasi autentikator (TOTP).') }}</p>

                <x-filament::button color="primary" wire:click="mulai">{{ __('Aktifkan Dua Faktor') }}</x-filament::button>
            @endif
        </div>
    </div>
</x-filament-panels::page>
