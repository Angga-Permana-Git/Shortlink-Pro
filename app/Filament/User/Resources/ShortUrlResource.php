<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use App\Services\Time\UserTimezoneService;
use App\Support\Enums\LinkStatus;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'slug';

    public static function getModelLabel(): string
    {
        return __('ui.panel.model_tautan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ui.panel.model_tautan');
    }

    public static function getNavigationLabel(): string
    {
        return __('ui.panel.navigasi_link_saya');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('owner_id', Filament::auth()->id());
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('ui.panel.detail_tautan'))
                    ->description(__('ui.panel.detail_tautan_catatan'))
                    ->schema([
                        TextInput::make('_slug')
                            ->label(__('ui.panel.nama_pendek_opsional'))
                            ->placeholder(__('ui.panel.placeholder_slug'))
                            ->prefix(fn () => rtrim(config('app.url'), '/').'/')
                            ->live()
                            ->helperText(fn ($state) => filled($state)
                                ? __('ui.panel.slug_jadi', ['url' => rtrim(config('app.url'), '/').'/'.$state])
                                : __('ui.panel.slug_acak'))
                            ->maxLength(100),
                        TextInput::make('destination_url')
                            ->label(__('ui.panel.url_tujuan'))
                            ->url()
                            ->required()
                            ->placeholder(__('ui.panel.placeholder_url_tujuan'))
                            ->columnSpanFull(),
                        Toggle::make('is_active')->label(__('ui.panel.aktif'))->default(true),
                        DateTimePicker::make('starts_at')->label(__('ui.panel.mulai_aktif')),
                        DateTimePicker::make('expires_at')->label(__('ui.panel.kedaluwarsa')),
                    ]),
                Section::make(__('ui.panel.proteksi_sandi'))
                    ->description(__('ui.panel.proteksi_sandi_catatan'))
                    ->schema([
                        Toggle::make('_protected')
                            ->label(__('ui.panel.lindungi_sandi'))
                            ->live()
                            ->default(fn (?ShortUrl $record) => filled($record?->password_hash)),
                        TextInput::make('_password')
                            ->label(__('ui.panel.kata_sandi_tautan'))
                            ->password()
                            ->revealable()
                            ->placeholder(__('ui.panel.placeholder_sandi'))
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
                    ->label(__('ui.panel.model_tautan'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->copyable()
                    ->copyableState(fn (ShortUrl $record) => $record->shortUrl())
                    ->tooltip(fn (ShortUrl $record) => __('ui.panel.tooltip_salin', ['url' => $record->shortUrl()]))
                    ->url(fn (ShortUrl $record) => route('redirect.show', $record->slug)),
                TextColumn::make('destination_url')->limit(45)->label(__('ui.panel.url_tujuan')),
                IconColumn::make('is_active')->boolean()->label(__('ui.panel.aktif')),
                TextColumn::make('total_clicks')->label(__('ui.panel.total_klik'))->sortable()->badge(),
                TextColumn::make('expires_at')->label(__('ui.panel.kedaluwarsa'))
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->expires_at, auth()->user()))
                    ->color(fn (ShortUrl $record) => $record->isExpired() ? 'danger' : 'gray'),
                TextColumn::make('created_at')->label(__('ui.panel.dibuat'))
                    ->formatStateUsing(fn (ShortUrl $record) => app(UserTimezoneService::class)
                        ->format($record->created_at, auth()->user()))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('ui.panel.status'))
                    ->options(fn () => collect(LinkStatus::cases())->mapWithKeys(fn ($c) => [$c->value => __("ui.panel.status_{$c->value}")])->all()),
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
