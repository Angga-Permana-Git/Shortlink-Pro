<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use App\Support\Enums\LinkStatus;
use App\Support\SlugGenerator;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortUrl::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $modelLabel = 'Short URL';

    protected static ?string $pluralModelLabel = 'Short URLs';

    protected static ?string $navigationLabel = 'My Links';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('owner_id', Filament::auth()->id());
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Link Details')
                    ->description('Buat short link milik Anda. Penghapusan link hanya dapat dilakukan Admin.')
                    ->schema([
                        TextInput::make('_slug')
                            ->label('Custom Slug (optional)')
                            ->placeholder('cth: promo-2026')
                            ->prefix(fn () => rtrim(config('app.url'), '/') . '/')
                            ->live()
                            ->helperText(fn ($state) => filled($state)
                                ? 'Short URL jadi: ' . rtrim(config('app.url'), '/') . '/' . $state
                                : 'Kosongkan untuk membuat slug acak otomatis.')
                            ->maxLength(100),
                        TextInput::make('destination_url')
                            ->label('Destination URL')
                            ->url()
                            ->required()
                            ->placeholder('https://example.com/halaman-tujuan')
                            ->columnSpanFull(),
                        Toggle::make('is_active')->label('Active')->default(true),
                        DateTimePicker::make('starts_at')->label('Starts at'),
                        DateTimePicker::make('expires_at')->label('Expires at'),
                    ]),
                Section::make('Password Protection')
                    ->description('Lindungi link dengan password. Password tidak pernah ditampilkan kembali.')
                    ->schema([
                        Toggle::make('_protected')
                            ->label('Protect with password')
                            ->live()
                            ->default(fn (ShortUrl|null $record) => filled($record?->password_hash)),
                        TextInput::make('_password')
                            ->label('Link password')
                            ->password()
                            ->revealable()
                            ->placeholder('cth: rahasia123')
                            ->required(fn ($get) => (bool) $get('_protected'))
                            ->visible(fn ($get) => (bool) $get('_protected')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('slug')
                    ->label('Short URL')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->copyable()
                    ->copyableState(fn (ShortUrl $record) => $record->shortUrl())
                    ->tooltip(fn (ShortUrl $record) => 'Klik untuk salin: '.$record->shortUrl())
                    ->url(fn (ShortUrl $record) => route('redirect.show', $record->slug)),
                TextColumn::make('destination_url')->limit(45),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('total_clicks')->label('Clicks')->sortable()->badge(),
                TextColumn::make('expires_at')->dateTime()->sortable()
                    ->color(fn (ShortUrl $record) => $record->isExpired() ? 'danger' : 'gray'),
                TextColumn::make('created_at')->date()->sortable()->label('Created'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(fn () => collect(LinkStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
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