<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LinktreeProfileResource\Pages;
use App\Filament\Concerns\BahasaSumberDaya;
use App\Models\LinktreeProfile;
use App\Support\Validation\AturanProfilLinktree;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LinktreeProfileResource extends Resource
{
    use BahasaSumberDaya;

    protected static ?string $model = LinktreeProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'display_name';

    protected static ?string $navigationLabel = 'Profil Linktree';

    protected static ?string $modelLabel = 'Profil Linktree';

    protected static ?string $pluralModelLabel = 'Profil Linktree';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('display_name')
                    ->label(__('Nama Tampilan'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('bio')
                    ->label(__('Keterangan'))
                    ->rows(3)
                    ->maxLength(500),
                TextInput::make('public_slug')
                    ->label(__('Alamat Publik'))
                    ->prefix('/u/')
                    ->maxLength(60)
                    ->rules(fn (?LinktreeProfile $record) => AturanProfilLinktree::alamatPublik($record?->user_id))
                    ->helperText(__('Huruf kecil, angka, dan tanda hubung. Kosongkan bila halaman belum ingin diterbitkan.')),
                Toggle::make('is_active')
                    ->label(__('Terbitkan halaman'))
                    ->helperText(__('Halaman publik hanya dapat dibuka bila profil diterbitkan dan alamatnya terisi.')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Pemilik'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Tidak diketahui')),
                TextColumn::make('display_name')
                    ->label(__('Nama Tampilan'))
                    ->searchable()
                    ->limit(30),
                TextColumn::make('public_slug')
                    ->label(__('Alamat Publik'))
                    ->searchable()
                    ->copyable()
                    ->placeholder(__('Belum diisi'))
                    ->formatStateUsing(fn ($state) => filled($state) ? '/u/'.$state : null),
                TextColumn::make('links_count')
                    ->counts('links')
                    ->badge()
                    ->label(__('Tautan')),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('Terbit')),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('Diperbarui')),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('Status Terbit'))
                    ->options([
                        1 => __('Terbit'),
                        0 => __('Belum terbit'),
                    ]),
                TernaryFilter::make('public_slug')
                    ->label(__('Alamat Publik'))
                    ->placeholder(__('Semua'))
                    ->trueLabel(__('Sudah diisi'))
                    ->falseLabel(__('Belum diisi')),
            ])
            ->actions([
                EditAction::make()->label(__('Sunting')),
                Action::make('buka')
                    ->label(__('Buka Halaman'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (LinktreeProfile $record) => filled($record->public_slug)
                        ? route('linktree.show', $record->public_slug)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (LinktreeProfile $record) => $record->is_active && filled($record->public_slug)),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLinktreeProfiles::route('/'),
            'edit' => Pages\EditLinktreeProfile::route('/{record}/edit'),
        ];
    }
}
