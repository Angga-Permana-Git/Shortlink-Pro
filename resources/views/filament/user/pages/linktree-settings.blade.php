<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                Simpan Halaman Linktree
            </x-filament::button>
        </div>
    </form>

    <x-filament::section>
        <x-slot name="heading">
            Alamat halaman publik
        </x-slot>

        <x-slot name="description">
            Bagikan alamat ini agar orang lain dapat melihat seluruh tautan Anda.
        </x-slot>

        @if ($this->publicUrl())
            <a href="{{ $this->publicUrl() }}" target="_blank" rel="noopener noreferrer">
                {{ $this->publicUrl() }}
            </a>
        @else
            <p>
                Alamat publik belum diisi, sehingga halaman belum bisa dibagikan.
                Isi kolom Alamat publik pada formulir di atas, lalu simpan.
            </p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
