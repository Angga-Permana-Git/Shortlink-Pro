<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use App\Support\Enums\LinkStatus;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortUrl::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $modelLabel = 'Short URL';

    protected static ?string $pluralModelLabel = 'Short URLs';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('slug')
                    ->required()
                    ->placeholder('cth: promo-2026')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                TextInput::make('destination_url')
                    ->required()
                    ->url()
                    ->placeholder('https://example.com/halaman-tujuan')
                    ->label('Destination URL')
                    ->columnSpanFull(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label('Owner'),
                Toggle::make('is_active')->default(true),
                DateTimePicker::make('starts_at')->label('Starts at'),
                DateTimePicker::make('expires_at')->label('Expires at'),
                Toggle::make('remove_password')
                    ->label('Remove password protection')
                    ->live()
                    ->default(false),
                TextInput::make('new_password')
                    ->label('Set new password')
                    ->password()
                    ->revealable()
                    ->placeholder('Kosongkan untuk tetap memakai password lama')
                    ->helperText('Fill to replace the current password. Ignore to keep it.')
                    ->dehydrated(false),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password_hash'] = ! empty($data['new_password'])
            ? Hash::make($data['new_password'])
            : null;

        unset($data['new_password'], $data['remove_password']);

        return $data;
    }

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
                    ->tooltip(fn (ShortUrl $record) => 'Klik untuk salin: '.$record->shortUrl())
                    ->url(fn (ShortUrl $record) => route('redirect.show', $record->slug)),
                TextColumn::make('owner.username')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_url')->limit(50)->toggleable(),
                IconColumn::make('is_active')->boolean()->label('Active')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('total_clicks')->sortable()->badge(),
                TextColumn::make('expires_at')->dateTime()->sortable()
                    ->color(fn (ShortUrl $record) => $record->isExpired() ? 'danger' : 'gray'),
                TextColumn::make('last_clicked_at')->dateTime()->placeholder('Never'),
            ])
            ->filters([
                SelectFilter::make('status')
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
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

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