@php
    $zona = (string) config('app.timezone', 'UTC');
    $label = match ($zona) {
        'Asia/Jakarta' => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Jayapura' => 'WIT',
        default => $zona,
    };
@endphp

<div
    class="fi-topbar-item flex items-center gap-1 px-2 text-xs font-medium text-gray-500 dark:text-gray-400"
    data-hook="jam-server"
    title="Waktu server {{ $zona }}"
    x-data="{
        dasar: {{ now()->getTimestampMs() }},
        lewat: 0,
        mulai: 0,
        init() {
            this.mulai = performance.now();
            setInterval(() => this.lewat = Math.floor(performance.now() - this.mulai), 1000);
        },
        get tampil() {
            return new Intl.DateTimeFormat('id-ID', {
                timeZone: @js($zona),
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(new Date(this.dasar + this.lewat));
        },
    }"
>
    <span aria-hidden="true">🕒</span>
    <span class="tabular-nums" x-text="tampil">{{ now()->format('H:i:s') }}</span>
    <span>{{ $label }}</span>
</div>
