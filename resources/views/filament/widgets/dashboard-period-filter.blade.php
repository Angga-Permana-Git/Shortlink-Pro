<div class="dasbor-periode">
    <div>
        <p class="dasbor-periode__judul">{{ __('Rentang dashboard') }}</p>
        <p class="dasbor-periode__ket">{{ __('Grafik, delta, dan tabel mengikuti rentang ini.') }}</p>
    </div>
    <div class="dasbor-periode__pilihan" role="group" aria-label="{{ __('Rentang dashboard') }}">
        @foreach ($pilihan as $nilai => $label)
            <a href="{{ request()->url().'?periode='.$nilai }}"
               class="dasbor-periode__tautan {{ $hariIni === $nilai ? 'is-aktif' : '' }}"
               @if ($hariIni === $nilai) aria-current="true" @endif>
                {{ __($label) }}
            </a>
        @endforeach
    </div>
</div>
