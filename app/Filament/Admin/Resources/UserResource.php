<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Concerns\AksiDukunganPengguna;
use App\Filament\Admin\Resources\UserResource\Concerns\FormPengguna;
use App\Filament\Admin\Resources\UserResource\Concerns\TabelPengguna;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class UserResource extends Resource
{
    use AksiDukunganPengguna;
    use BahasaSumberDaya;
    use FormPengguna;
    use TabelPengguna;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShortUrlsRelationManager::class,
            RelationManagers\LoginAuditLogsRelationManager::class,
            RelationManagers\AuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
