<?php

namespace App\Filament\Admin\Resources\UserResource\Concerns;

use App\Models\User;
use App\Support\Enums\Role;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait TabelPengguna
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('name')->label(__('Nama'))->searchable()->sortable(),
                TextColumn::make('username')->searchable()->label(__('Nama Pengguna')),
                TextColumn::make('auth_provider')->label(__('Sumber Akun'))->badge()->sortable(),
                TextColumn::make('role')->label(__('Peran'))->badge()->color(fn ($state) => $state === Role::Admin->value ? 'danger' : 'gray'),
                TextColumn::make('is_protected')
                    ->label(__('Dilindungi'))
                    ->badge()
                    ->colors([
                        'danger' => true,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? __('Dilindungi') : '-'),
                ToggleColumn::make('is_active')->label(__('Aktif')),
                TextColumn::make('last_login_at')->dateTime()->sortable()->placeholder(__('Belum pernah'))->label(__('Login terakhir')),
                TextColumn::make('created_at')->dateTime()->sortable()->label(__('Terdaftar')),
            ])
            ->filters([
                SelectFilter::make('role')->label(__('Peran'))->options(Role::options()),
                SelectFilter::make('auth_provider')->label(__('Sumber Akun'))
                    ->options(['keycloak' => 'Keycloak', 'local' => __('Lokal')]),
                SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        1 => __('Aktif'),
                        0 => __('Nonaktif'),
                    ]),
                Filter::make('tanpa_2fa')
                    ->label(__('Admin tanpa 2FA'))
                    ->query(fn (Builder $query) => $query->where('role', Role::Admin->value)->whereNull('two_factor_confirmed_at')),
                Filter::make('tanpa_tautan')
                    ->label(__('Belum punya tautan'))
                    ->query(fn (Builder $query) => $query->doesntHave('shortUrls')),
                Filter::make('nonaktif_bertantau')
                    ->label(__('Nonaktif dengan tautan aktif'))
                    ->query(fn (Builder $query) => $query
                        ->where('is_active', false)
                        ->whereHas('shortUrls', fn (Builder $q) => $q->where('is_active', true))),
                TrashedFilter::make()->label(__('Data terhapus')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                static::aksiKirimReset(),
                static::aksiKeluarkanSesi(),
                static::aksiMasukSebagai(),
                RestoreAction::make()->label(__('Pulihkan')),
                DeleteAction::make()
                    ->label(__('Hapus'))
                    ->modalDescription(__('Akun dipindahkan ke data terhapus dan semua tautannya dinonaktifkan. Data masih dapat dipulihkan.'))
                    ->visible(fn (User $record) => static::bolehDihapus($record)),
                ForceDeleteAction::make()
                    ->label(__('Hapus Permanen'))
                    ->modalDescription(__('Akun, tautan, dan seluruh riwayat kliknya dihapus permanen dan tidak dapat dipulihkan.'))
                    ->visible(fn (User $record) => $record->trashed() && static::bolehDihapus($record)),
            ])
            ->bulkActions([
                RestoreBulkAction::make()->label(__('Pulihkan')),
                DeleteBulkAction::make()
                    ->label(__('Hapus'))
                    ->authorizeIndividualRecords(fn (User $record) => static::bolehDihapus($record)),
                ForceDeleteBulkAction::make()
                    ->label(__('Hapus Permanen'))
                    ->authorizeIndividualRecords(fn (User $record) => static::bolehDihapus($record)),
            ]);
    }

    /**
     * Akun terlindungi dan akun sendiri tidak boleh dihapus, baik lewat aksi
     * baris maupun aksi massal.
     */
    public static function bolehDihapus(User $record): bool
    {
        return ! $record->is_protected && $record->id !== auth()->id();
    }
}
