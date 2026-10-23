<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\RoleChangeLog;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $oldRole = null;

    protected function beforeSave(): void
    {
        $this->oldRole = $this->record->getOriginal('role') ?? $this->record->role;
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        /** @var User $actor */
        $actor = auth()->user();

        /** @var User $target */
        $target = $this->record;

        if ($target->is_protected && $actor->auth_provider !== 'local') {
            Notification::make()
                ->danger()
                ->title(__('Akun terlindungi hanya dapat diubah oleh admin lokal'))
                ->send();

            $this->redirect(ListUsers::getUrl());

            return;
        }

    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->id === auth()->id()) {
            $data['role'] = $this->record->role;
            $data['is_protected'] = $this->record->is_protected;
            $data['is_active'] = $this->record->is_active;
        }

        // Pelaku non-lokal tidak boleh mengubah akun terlindungi (penjaga tambahan).
        if ($this->record->is_protected && auth()->user()?->auth_provider !== 'local') {
            $data['role'] = $this->record->role;
            $data['is_protected'] = true;
            $data['is_active'] = $this->record->is_active;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;
        $actor = auth()->user();

        $newRole = $record->role;
        $oldRole = $this->oldRole ?? $record->getOriginal('role');

        if ($oldRole !== $newRole) {
            RoleChangeLog::create([
                'actor_user_id' => $actor?->id,
                'target_user_id' => $record->id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);

            Notification::make()
                ->success()
                ->title(__('Peran diperbarui dari :lama menjadi :baru', ['lama' => $oldRole, 'baru' => $newRole]))
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
