<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShortUrlResource\Concerns\DetailTautan;
use App\Filament\Admin\Resources\ShortUrlResource\Concerns\FormTautan;
use App\Filament\Admin\Resources\ShortUrlResource\Concerns\TabelTautan;
use App\Filament\Admin\Resources\ShortUrlResource\Pages;
use App\Filament\Admin\Resources\ShortUrlResource\RelationManagers\ClickEventsRelationManager;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\ShortUrl;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class ShortUrlResource extends Resource
{
    use BahasaSumberDaya;
    use DetailTautan;
    use FormTautan;
    use TabelTautan;

    public static function getRelations(): array
    {
        return [
            ClickEventsRelationManager::class,
        ];
    }

    protected static ?string $model = ShortUrl::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $modelLabel = 'Tautan Pendek';

    protected static ?string $pluralModelLabel = 'Tautan Pendek';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShortUrls::route('/'),
            'create' => Pages\CreateShortUrl::route('/create'),
            'view' => Pages\ViewShortUrl::route('/{record}'),
            'edit' => Pages\EditShortUrl::route('/{record}/edit'),
        ];
    }
}
