<x-filament-widgets::widget>
    <x-filament::section heading="{{ __('Perlu Perhatian') }}" description="{{ __('Pilih kartu untuk membuka daftar yang berkaitan.') }}">
        @if ($items === [])
            <div class="dasbor-perhatian__kosong">
                <x-filament::icon icon="heroicon-o-check-circle" style="width: 1.5rem; height: 1.5rem; flex: 0 0 auto;" />
                <p style="margin: 0;">{{ __('Tidak ada masalah yang terdeteksi saat ini.') }}</p>
            </div>
        @else
            <div class="dasbor-perhatian">
                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" class="dasbor-perhatian__item">
                        <span class="dasbor-perhatian__ikon">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" style="width: 1.25rem; height: 1.25rem;" />
                        </span>
                        <span class="dasbor-perhatian__isi">
                            <span class="dasbor-perhatian__kepala">
                                <span class="dasbor-perhatian__judul">{{ $item['judul'] }}</span>
                                <span class="dasbor-perhatian__jumlah">{{ $item['jumlah'] }}</span>
                            </span>
                            <span class="dasbor-perhatian__pesan">{{ $item['pesan'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
