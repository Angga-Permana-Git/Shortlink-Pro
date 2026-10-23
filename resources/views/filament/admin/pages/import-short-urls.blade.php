<x-filament-panels::page>
    <div class="fi-section">
        <div class="fi-section-content p-6 space-y-4">
            <form wire:submit="import" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit" color="primary">
                    {{ __('Impor Tautan') }}
                </x-filament::button>
            </form>

            @if ($berhasil !== null)
                <div class="fi-section">
                    <div class="fi-section-content p-4">
                        <p><strong>{{ $berhasil }}</strong> {{ __('tautan berhasil diimpor.') }}</p>

                        @if ($gagal !== [])
                            <p class="mt-2 font-semibold">{{ __(':jumlah baris gagal:', ['jumlah' => count($gagal)]) }}</p>
                            <ul class="mt-1 list-disc pl-6 text-sm">
                                @foreach ($gagal as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
