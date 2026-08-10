<x-filament-panels::page>
    <style>
        .ts-layout { display: flex; flex-direction: column; gap: 1.25rem; }
        .ts-layout > * { flex: 1 1 0; min-width: 0; }
        @media (min-width: 768px) {
            .ts-layout { flex-direction: row; align-items: stretch; }
        }
        .ts-card { height: 100%; }
        .ts-clock { font-size: 2.2rem; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1.3; }
        .ts-clock .ts-date { font-size: 1.1rem; font-weight: 600; display: block; }
        .ts-clock .ts-time { font-size: 3rem; font-weight: 800; display: block; letter-spacing: .02em; }
        .ts-clock .ts-tz { font-size: 1.05rem; font-weight: 500; color: #6b7280; margin-left: .5rem; }
    </style>
    <div class="ts-layout">
        <div class="ts-card">
            <div class="fi-section">
                <div class="fi-section-content p-6">
                    <div class="ts-clock" x-data="{ now: new Date(), tz: @js(old('timezone', app(\App\Services\System\TimezoneService::class)->get())) }"
                         x-init="setInterval(() => now = new Date(), 1000)">
                        <span class="ts-date" x-text="Intl.DateTimeFormat('id-ID', { timeZone: tz, dateStyle: 'full' }).format(now)"></span>
                        <span class="ts-time" x-text="Intl.DateTimeFormat('id-ID', { timeZone: tz, hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(now)"></span>
                        <span class="ts-tz" x-text="tz"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ts-card">
            <div class="fi-section">
                <div class="fi-section-content p-6">
                    <form wire:submit="save" class="space-y-4">
                        {{ $this->form }}

                        <div>
                            <x-filament::button type="submit" color="primary">
                                Simpan Zona Waktu
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>