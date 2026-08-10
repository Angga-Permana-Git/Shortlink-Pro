<?php

namespace App\Filament\Admin\Pages;

use App\Services\Branding\BrandingService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class BrandingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected string $view = 'filament.admin.pages.branding-settings';

    protected static ?string $title = 'Branding';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => \App\Models\Setting::get('app_name', config('app.name')),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('app_name')
                    ->label('Nama aplikasi')
                    ->required()
                    ->placeholder('cth: Shortlink Enterprise')
                    ->maxLength(255),
                FileUpload::make('login_logo')
                    ->label('Unggah Logo Baru (PNG/JPG/SVG)')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->directory('branding')
                    ->disk('public')
                    ->visibility('public')
                    ->helperText('Ditampilkan di halaman login dan header. Format PNG, JPG, atau SVG. Maksimal 2MB.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $updateData = [];

        if (! empty($data['app_name'])) {
            $updateData['app_name'] = $data['app_name'];
        }

        if (! empty($data['login_logo'])) {
            $updateData['login_logo'] = $data['login_logo'];
        }

        \App\Models\Setting::set('updated_by', auth()->id());

        app(BrandingService::class)->updateBranding(auth()->user(), $updateData);

        $this->form->fill([
            'app_name' => \App\Models\Setting::get('app_name', config('app.name')),
            'login_logo' => null,
        ]);

        Notification::make()->success()->title('Pengaturan tersimpan')->send();
    }

    public function deleteLogo(): void
    {
        $path = \App\Models\Setting::get('login_logo');

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        \App\Models\Setting::set('login_logo', null);
        \App\Models\Setting::set('updated_by', auth()->id());

        $this->form->fill([
            'app_name' => \App\Models\Setting::get('app_name', config('app.name')),
            'login_logo' => null,
        ]);

        \App\Services\Audit\AuditLogger::log(auth()->user(), 'branding.logo_delete', 'setting');

        Notification::make()->success()->title('Logo berhasil dihapus secara bersih')->send();
    }
}