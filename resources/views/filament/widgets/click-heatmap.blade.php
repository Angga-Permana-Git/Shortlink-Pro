<x-filament-widgets::widget>
    <x-filament::section heading="{{ __('Peta Klik per Hari dan Jam') }}" description="{{ __('Waktu server. Warna makin pekat berarti klik makin banyak.') }}">
        <div class="dasbor-heatmap__scroll">
            <div class="dasbor-heatmap__inner">
                <div class="dasbor-heatmap__baris">
                    <span></span>
                    @for ($jam = 0; $jam < 24; $jam++)
                        <span class="dasbor-heatmap__jam">{{ $jam % 3 === 0 ? $jam : '' }}</span>
                    @endfor
                </div>
                @foreach ($peta['grid'] as $indeks => $baris)
                    <div class="dasbor-heatmap__baris">
                        <span class="dasbor-heatmap__label">{{ __($peta['hari'][$indeks]) }}</span>
                        @foreach ($baris as $jumlah)
                            <span class="dasbor-heatmap__sel"
                                  style="opacity: {{ $jumlah > 0 ? max(0.2, $jumlah / max(1, $peta['maks'])) : 0.08 }}"
                                  title="{{ __($peta['hari'][$indeks]) }} {{ sprintf('%02d.00', $loop->index) }}: {{ __(':jumlah klik', ['jumlah' => $jumlah]) }}"></span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
