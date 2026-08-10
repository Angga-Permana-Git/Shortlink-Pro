<?php

namespace App\Filament\Admin\Pages;

use App\Services\System\TimezoneService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TimezoneSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected string $view = 'filament.admin.pages.timezone-settings';

    protected static ?string $title = 'Pengaturan Waktu';

    protected static ?string $navigationLabel = 'Pengaturan Waktu';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'timezone' => app(TimezoneService::class)->get(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('timezone')
                    ->label('Zona Waktu Server')
                    ->options($this->timezoneOptions())
                    ->searchable()
                    ->required()
                    ->helperText('Sesuaikan zona waktu aplikasi dengan waktu PC/lingkungan Anda.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $timezone = (string) ($this->form->getState()['timezone'] ?? '');

        $ok = app(TimezoneService::class)->set($timezone, auth()->id());

        if (! $ok) {
            Notification::make()->danger()->title('Zona waktu tidak valid')->send();

            return;
        }

        \App\Services\Audit\AuditLogger::log(
            auth()->user(),
            'settings.timezone.update',
            'setting',
            null,
            ['timezone' => $timezone],
        );

        Notification::make()->success()->title('Zona waktu berhasil diubah')->send();
    }

    private function timezoneOptions(): array
    {
        $regions = [
            'Asia/Jakarta' => 'WIB (UTC+7) — Jakarta',
            'Asia/Makassar' => 'WITA (UTC+8) — Makassar',
            'Asia/Jayapura' => 'WIT (UTC+9) — Jayapura',
            'Asia/Kuala_Lumpur' => 'Malaysia (UTC+8)',
            'Asia/Singapore' => 'Singapore (UTC+8)',
            'Asia/Bangkok' => 'Thailand (UTC+7)',
            'Asia/Tokyo' => 'Japan (UTC+9)',
            'Asia/Seoul' => 'Korea (UTC+9)',
            'UTC' => 'UTC',
            'America/New_York' => 'New York (UTC-5)',
            'Europe/London' => 'London (UTC+0)',
        ];

        return collect(timezone_identifiers_list())
            ->filter(fn ($tz) => ! array_key_exists($tz, $regions))
            ->mapWithKeys(fn ($tz) => [$tz => $tz])
            ->sort()
            ->prepend($regions)
            ->all();
    }
}