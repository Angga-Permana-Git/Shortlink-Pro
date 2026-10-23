<?php

namespace App\Filament\Widgets;

use App\Models\ShortUrl;
use App\Services\Admin\AdminDashboardService;
use App\Services\Time\UserTimezoneService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Menampilkan tautan dari pengguna yang zona waktunya berbeda dengan server.
 *
 * Waktu dibuat, mulai, dan kedaluwarsa ditampilkan dalam zona pengguna beserta
 * waktu server sebagai pembanding, sehingga admin tidak kehilangan konteks.
 */
class AdminForeignLinksTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Tautan Pengguna Lintas Zona Waktu'))
            ->description(__('Waktu lokal pengguna dan waktu server ditampilkan berdampingan.'))
            ->query(app(AdminDashboardService::class)->tautanLuarNegeriQuery())
            ->columns([
                TextColumn::make('owner.name')
                    ->label(__('Pengguna'))
                    ->description(fn (ShortUrl $record): ?string => $record->owner?->email)
                    ->searchable(),
                TextColumn::make('owner.timezone')
                    ->label(__('Zona Waktu'))
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? app(UserTimezoneService::class)->label($state)
                        : '-')
                    ->description(fn (ShortUrl $record): ?string => $record->owner?->timezone),
                TextColumn::make('slug')
                    ->label(__('Tautan'))
                    ->copyable()
                    ->url(fn (ShortUrl $record): string => route('redirect.show', $record->slug)),
                TextColumn::make('destination_url')->label(__('URL Tujuan'))->limit(35),
                TextColumn::make('created_at')
                    ->label(__('Dibuat (pengguna)'))
                    ->formatStateUsing(fn (ShortUrl $record): string => $this->waktu($record, 'created_at'))
                    ->description(fn (ShortUrl $record): string => $this->waktuServer($record, 'created_at')),
                TextColumn::make('starts_at')
                    ->label(__('Mulai (pengguna)'))
                    ->formatStateUsing(fn (ShortUrl $record): string => $this->waktu($record, 'starts_at'))
                    ->description(fn (ShortUrl $record): string => $this->waktuServer($record, 'starts_at'))
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label(__('Kedaluwarsa (pengguna)'))
                    ->formatStateUsing(fn (ShortUrl $record): string => $this->waktu($record, 'expires_at'))
                    ->description(fn (ShortUrl $record): string => $this->waktuServer($record, 'expires_at')),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateIcon('heroicon-o-globe-alt')
            ->emptyStateHeading(__('Belum ada tautan lintas zona waktu'))
            ->emptyStateDescription(__('Tautan akan tampil di sini setelah pengguna dengan zona waktu berbeda membuat tautan.'));
    }

    private function waktu(ShortUrl $record, string $kolom): string
    {
        if (! $record->owner || ! $record->{$kolom}) {
            return __('Tidak ada');
        }

        return app(UserTimezoneService::class)->format($record->{$kolom}, $record->owner);
    }

    private function waktuServer(ShortUrl $record, string $kolom): string
    {
        if (! $record->{$kolom}) {
            return __('Tidak ada');
        }

        return __('Server: :waktu', ['waktu' => $record->{$kolom}->format('d M Y H:i').' '.config('app.timezone')]);
    }
}
