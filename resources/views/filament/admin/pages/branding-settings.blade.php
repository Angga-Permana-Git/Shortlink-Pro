<x-filament-panels::page>
    <form wire:submit="save" class="fi-form space-y-6">
        {{ $this->form }}

        <div style="margin-top: 1.5rem;">
            <x-filament::button type="submit" color="primary">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>

    @php
        $currentLogoUrl = app(\App\Services\Branding\BrandingService::class)->loginLogoUrl();
        $currentLogoPath = \App\Models\Setting::get('login_logo');
    @endphp

    <div style="margin-top: 2rem;">
        <x-filament::section>
            <x-slot name="heading">
                Logo Terpasang Saat Ini
            </x-slot>
            <x-slot name="description">
                Preview logo yang saat ini aktif digunakan pada halaman login dan header aplikasi.
            </x-slot>

            @if($currentLogoUrl)
                <div style="padding: 1.25rem; background: rgba(0,0,0,0.03); border-radius: 0.75rem; border: 1px dashed rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; min-height: 120px;">
                    <img src="{{ $currentLogoUrl }}" alt="Logo Terpasang" style="max-height: 90px; width: auto; max-width: 100%; object-fit: contain;">
                </div>

                <div style="font-size: 0.8rem; opacity: 0.7; font-family: monospace; margin-bottom: 1.25rem; word-break: break-all;">
                    File Path: {{ $currentLogoPath ?: 'Default (logo.png)' }}
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <x-filament::button
                        tag="a"
                        href="{{ $currentLogoUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        color="gray"
                        icon="heroicon-m-arrow-top-right-on-square"
                    >
                        Lihat Logo Full
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        wire:click="deleteLogo"
                        wire:confirm="Apakah Anda yakin ingin menghapus logo ini secara bersih?"
                        color="danger"
                        icon="heroicon-m-trash"
                    >
                        Hapus Logo
                    </x-filament::button>
                </div>
            @else
                <div style="padding: 1.5rem; text-align: center; opacity: 0.65; font-size: 0.875rem;">
                    Belum ada logo khusus yang diunggah. Nama Aplikasi akan ditampilkan dalam bentuk teks.
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>