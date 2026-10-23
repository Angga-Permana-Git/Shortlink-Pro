<x-filament-panels::page>
    <form wire:submit="save" class="fi-form space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit" color="primary">
                {{ __('Simpan Pengaturan') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
