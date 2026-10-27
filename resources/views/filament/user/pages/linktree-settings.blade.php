<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                {{ __('ui.panel.simpan_halaman_linktree') }}
            </x-filament::button>
        </div>
    </form>

    <x-filament::section>
        <x-slot name="heading">
            {{ __('ui.panel.alamat_halaman_publik') }}
        </x-slot>

        <x-slot name="description">
            {{ __('ui.panel.bagikan_alamat') }}
        </x-slot>

        @if ($this->publicUrl())
            <a href="{{ $this->publicUrl() }}" target="_blank" rel="noopener noreferrer">
                {{ $this->publicUrl() }}
            </a>
        @else
            <p>{{ __('ui.panel.alamat_belum_form') }}</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
