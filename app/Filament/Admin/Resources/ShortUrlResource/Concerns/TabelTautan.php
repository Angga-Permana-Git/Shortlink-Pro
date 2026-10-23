<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Concerns;

use App\Models\ShortUrl;
use App\Support\Enums\LinkStatus;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Daftar tautan di panel admin: kolom, filter status, dan aksi baris.
 *
 * Dipisah dari ShortUrlResource supaya berkas resource tetap di bawah 150 baris
 * (disiplin_struktur.md bagian 2); perilakunya tidak berubah.
 */
trait TabelTautan
{
    use AksiMassalTautan;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->copyable()
                    ->copyableState(fn (ShortUrl $record) => $record->shortUrl())
                    ->tooltip(fn (ShortUrl $record) => __('Klik untuk salin: ').$record->shortUrl())
                    ->url(fn (ShortUrl $record) => route('redirect.show', $record->slug))
                    ->label(__('Tautan Pendek')),
                TextColumn::make('owner.username')
                    ->label(__('Pemilik'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_url')->limit(50)->toggleable()->label(__('URL Tujuan')),
                IconColumn::make('is_active')->boolean()->label(__('Aktif'))
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('total_clicks')->sortable()->badge()->label(__('Total Klik')),
                TextColumn::make('expires_at')->dateTime()->sortable()->label(__('Kedaluwarsa'))
                    ->color(fn (ShortUrl $record) => $record->isExpired() ? 'danger' : 'gray'),
                TextColumn::make('last_clicked_at')->dateTime()->placeholder(__('Belum pernah'))->label(__('Klik terakhir')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(fn () => collect(LinkStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all())
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $time = now();

                        match ($data['value']) {
                            LinkStatus::Active->value => $query->where('is_active', true)
                                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $time))
                                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $time)),
                            LinkStatus::Inactive->value => $query->where('is_active', false),
                            LinkStatus::Expired->value => $query->whereNotNull('expires_at')->where('expires_at', '<=', $time),
                            LinkStatus::Protected->value => $query->whereNotNull('password_hash'),
                            LinkStatus::Scheduled->value => $query->whereNotNull('starts_at')->where('starts_at', '>', $time),
                            default => null,
                        };
                    }),
                SelectFilter::make('owner_id')
                    ->label(__('Pemilik'))
                    ->relationship('owner', 'username')
                    ->searchable(),
                Filter::make('dibuat')
                    ->label(__('Tanggal dibuat'))
                    ->schema([
                        DatePicker::make('dari')->label(__('Dari')),
                        DatePicker::make('sampai')->label(__('Sampai')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '>=', $tanggal))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $tanggal) => $q->whereDate('created_at', '<=', $tanggal))),
                Filter::make('kedaluwarsa_7')
                    ->label(__('Kedaluwarsa ≤7 hari'))
                    ->query(fn (Builder $query) => $query->where('is_active', true)->whereBetween('expires_at', [now(), now()->addDays(7)])),
                Filter::make('lintas_zona')
                    ->label(__('Pemilik lintas zona waktu'))
                    ->query(fn (Builder $query) => $query->whereHas('owner', fn (Builder $q) => $q
                        ->whereNotNull('timezone')
                        ->where('timezone', '!=', config('app.timezone')))),
                TrashedFilter::make()->label(__('Data terhapus')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('qr')
                    ->label(__('QR'))
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (ShortUrl $record): string => route('qr.show', $record->slug))
                    ->openUrlInNewTab(),
                RestoreAction::make()->label(__('Pulihkan')),
                DeleteAction::make()
                    ->label(__('Hapus'))
                    ->modalDescription(__('Tautan dipindahkan ke data terhapus sehingga tidak dapat dibuka, tetapi riwayat kliknya tetap tersimpan.'))
                    ->visible(fn (ShortUrl $record) => ! $record->trashed()),
                ForceDeleteAction::make()
                    ->label(__('Hapus Permanen'))
                    ->modalDescription(__('Tautan dan seluruh riwayat kliknya dihapus permanen.'))
                    ->visible(fn (ShortUrl $record) => $record->trashed()),
            ])
            ->bulkActions([
                ...static::aksiMassalTautan(),
                RestoreBulkAction::make()->label(__('Pulihkan')),
                DeleteBulkAction::make()->label(__('Hapus')),
                ForceDeleteBulkAction::make()->label(__('Hapus Permanen')),
            ]);
    }
}
