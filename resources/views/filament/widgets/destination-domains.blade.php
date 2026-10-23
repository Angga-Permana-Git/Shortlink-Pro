<x-filament-widgets::widget>
    <x-filament::section heading="{{ __('Domain Tujuan Teratas') }}" description="{{ __('Domain yang paling sering menjadi tujuan tautan.') }}">
        @forelse ($domain as $baris)
            <div class="dasbor-domain__baris">
                <div class="dasbor-domain__kepala">
                    <span class="dasbor-domain__nama">{{ $baris['domain'] }}</span>
                    <span class="dasbor-domain__angka">{{ __(':klik klik · :tautan tautan', ['klik' => $baris['klik'], 'tautan' => $baris['tautan']]) }}</span>
                </div>
                <div class="dasbor-domain__track">
                    <div class="dasbor-domain__isi"
                         style="width: {{ max(2, $domain[0]['klik'] > 0 ? ($baris['klik'] / $domain[0]['klik']) * 100 : 0) }}%"></div>
                </div>
            </div>
        @empty
            <p class="dasbor-kosong">{{ __('Belum ada tautan dengan domain tujuan.') }}</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
