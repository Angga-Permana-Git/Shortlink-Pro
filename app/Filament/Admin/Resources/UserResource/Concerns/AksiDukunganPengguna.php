<?php

namespace App\Filament\Admin\Resources\UserResource\Concerns;

use App\Models\User;
use App\Services\Admin\AdminUserSupportService;
use App\Support\Enums\ResetLinkOutcome;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\RedirectResponse;

trait AksiDukunganPengguna
{
    protected static function aksiKirimReset(): Action
    {
        return Action::make('kirim_reset')
            ->label(__('Kirim Reset Kata Sandi'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->requiresConfirmation()
            ->modalDescription(__('Tautan atur ulang kata sandi akan dikirim ke email pengguna.'))
            ->visible(fn (User $record): bool => ! $record->trashed() && $record->isLocal() && $record->isActive() && filled($record->email))
            ->action(fn (User $record) => static::kirimReset($record));
    }

    protected static function aksiKeluarkanSesi(): Action
    {
        return Action::make('keluarkan_sesi')
            ->label(__('Keluarkan Sesi'))
            ->icon(Heroicon::OutlinedArrowRightOnRectangle)
            ->requiresConfirmation()
            ->modalDescription(__('Semua sesi pengguna ini akan diakhiri dan ia harus masuk kembali.'))
            ->visible(fn (User $record): bool => ! $record->trashed() && $record->id !== auth()->id())
            ->action(fn (User $record) => static::keluarkanSesi($record));
    }

    protected static function aksiMasukSebagai(): Action
    {
        return Action::make('masuk_sebagai')
            ->label(__('Masuk Sebagai'))
            ->icon(Heroicon::OutlinedUserCircle)
            ->requiresConfirmation()
            ->modalDescription(__('Anda akan masuk sebagai pengguna ini. Kembali ke akun admin melalui banner di halaman pengguna.'))
            ->visible(fn (User $record): bool => ! $record->trashed() && $record->isActive() && ! $record->isProtected() && $record->id !== auth()->id())
            ->action(fn (User $record): RedirectResponse => static::masukSebagai($record));
    }

    protected static function kirimReset(User $record): void
    {
        $hasil = app(AdminUserSupportService::class)->kirimTautanReset($record, auth()->user());

        $judul = match ($hasil->outcome) {
            ResetLinkOutcome::Sent => __('Tautan reset kata sandi dikirim'),
            ResetLinkOutcome::Throttled => __('Permintaan terlalu sering, coba lagi nanti'),
            ResetLinkOutcome::ExternalAccount => __('Akun ini memakai penyedia luar'),
            ResetLinkOutcome::UnknownEmail => __('Email pengguna tidak ditemukan'),
        };

        Notification::make()
            ->{$hasil->outcome === ResetLinkOutcome::Sent ? 'success' : 'warning'}()
            ->title($judul)
            ->body($hasil->previewUrl ? __('Pratinjau: :url', ['url' => $hasil->previewUrl]) : null)
            ->send();
    }

    protected static function keluarkanSesi(User $record): void
    {
        $jumlah = app(AdminUserSupportService::class)->keluarkanSesi($record, auth()->user());

        Notification::make()
            ->success()
            ->title(__('Sesi dikeluarkan'))
            ->body(__(':jumlah sesi aktif dihapus.', ['jumlah' => $jumlah]))
            ->send();
    }

    protected static function masukSebagai(User $record): RedirectResponse
    {
        return app(AdminUserSupportService::class)->mulaiImpersonasi($record, auth()->user());
    }
}
