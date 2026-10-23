<x-filament-panels::page>
    <form wire:submit="save" class="fi-form space-y-6">
        {{ $this->form }}

        <div class="branding-actions">
            <x-filament::button type="submit" color="primary">
                {{ __('Simpan Perubahan') }}
            </x-filament::button>
        </div>
    </form>

    <div class="branding-preview">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('Logo Terpasang Saat Ini') }}
            </x-slot>
            <x-slot name="description">
                {{ __('Preview logo yang saat ini aktif digunakan pada halaman login dan header aplikasi.') }}
            </x-slot>

            @if($currentLogoUrl)
                <div class="branding-preview__box">
                    <img src="{{ $currentLogoUrl }}" alt="{{ __('Logo Terpasang') }}" class="branding-preview__image">
                </div>

                <div class="branding-preview__path">
                    {{ __('File Path: :path', ['path' => $currentLogoPath ?: __('Default (logo.png)')]) }}
                </div>

                <div class="branding-preview__actions">
                    <x-filament::button
                        tag="a"
                        href="{{ $currentLogoUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        color="gray"
                        icon="heroicon-m-arrow-top-right-on-square"
                    >
                        {{ __('Lihat Logo Full') }}
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        wire:click="deleteLogo"
                        wire:confirm="{{ __('Apakah Anda yakin ingin menghapus logo ini?') }}"
                        color="danger"
                        icon="heroicon-m-trash"
                    >
                        {{ __('Hapus Logo') }}
                    </x-filament::button>
                </div>
            @else
                <div class="branding-preview__empty">
                    {{ __('Belum ada logo khusus yang diunggah. Nama Aplikasi akan ditampilkan dalam bentuk teks.') }}
                </div>
            @endif
        </x-filament::section>
    </div>

    <style>
        .branding-actions { margin-top: 1.5rem; }
        .branding-preview { margin-top: 2rem; }
        .branding-preview__box {
            padding: 1.25rem;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 0.75rem;
            border: 1px dashed rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            min-height: 120px;
        }
        .branding-preview__image { max-height: 90px; width: auto; max-width: 100%; object-fit: contain; }
        .branding-preview__path {
            font-size: 0.8rem;
            opacity: 0.7;
            font-family: monospace;
            margin-bottom: 1.25rem;
            word-break: break-all;
        }
        .branding-preview__actions { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
        .branding-preview__empty { padding: 1.5rem; text-align: center; opacity: 0.65; font-size: 0.875rem; }
    </style>
</x-filament-panels::page>
