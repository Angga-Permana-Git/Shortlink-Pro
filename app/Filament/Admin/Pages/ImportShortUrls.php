<?php

namespace App\Filament\Admin\Pages;

use App\Services\Admin\AdminShortUrlImportService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ImportShortUrls extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected string $view = 'filament.admin.pages.import-short-urls';

    protected static ?string $title = 'Impor Tautan';

    protected static ?string $navigationLabel = 'Impor Tautan';

    public function getTitle(): string
    {
        return __('Impor Tautan');
    }

    public static function getNavigationLabel(): string
    {
        return __('Impor Tautan');
    }

    public array $data = [];

    public ?int $berhasil = null;

    /** @var array<int, string> */
    public array $gagal = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('file')
                    ->label(__('Berkas CSV'))
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->maxSize(5120)
                    ->helperText(__('Header wajib: destination_url. Opsional: slug, owner_email, is_active, expires_at (YYYY-MM-DD HH:MM).')),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $nilai = $data['file'] ?? null;
        $path = is_array($nilai) ? (string) reset($nilai) : (string) $nilai;

        if ($path === '' || ! Storage::disk('local')->exists($path)) {
            Notification::make()->danger()->title(__('Berkas tidak ditemukan'))->send();

            return;
        }

        $hasil = app(AdminShortUrlImportService::class)->import(
            Storage::disk('local')->path($path),
            auth()->user(),
        );

        Storage::disk('local')->delete($path);

        $this->berhasil = $hasil['berhasil'];
        $this->gagal = $hasil['gagal'];
        $this->form->fill(['file' => []]);

        Notification::make()
            ->success()
            ->title(__(':jumlah tautan diimpor', ['jumlah' => $hasil['berhasil']]))
            ->body(__(':jumlah baris gagal.', ['jumlah' => count($hasil['gagal'])]))
            ->send();
    }
}
