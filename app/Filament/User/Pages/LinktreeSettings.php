<?php

namespace App\Filament\User\Pages;

use App\Dto\LinktreeLinkDTO;
use App\Dto\UpdateLinktreeProfileDTO;
use App\Services\Linktree\LinktreeService;
use App\Support\Validation\AturanProfilLinktree;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Pengaturan halaman Linktree milik pengguna yang sedang masuk.
 *
 * Halaman ini hanya menangani tampilan dan pengumpulan data; seluruh aturan
 * penyimpanan berada di LinktreeService.
 */
class LinktreeSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected string $view = 'filament.user.pages.linktree-settings';

    public static function getNavigationLabel(): string
    {
        return __('ui.panel.navigasi_linktree');
    }

    public function getTitle(): string
    {
        return __('ui.panel.judul_linktree');
    }

    public array $data = [];

    public function mount(): void
    {
        $profile = $this->service()->profile(auth()->user());

        $this->form->fill([
            'display_name' => $profile->display_name,
            'bio' => $profile->bio,
            'public_slug' => $profile->public_slug,
            'is_active' => $profile->is_active,
            'links' => $profile->links->map(fn ($link): array => [
                'label' => $link->label,
                'url' => $link->url,
                'is_active' => $link->is_active,
            ])->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('display_name')
                    ->label(__('ui.panel.nama_publik'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('public_slug')
                    ->label(__('ui.panel.alamat_publik'))
                    ->helperText(__('ui.panel.helper_alamat_publik'))
                    ->prefix('u/')
                    ->required()
                    ->maxLength(60)
                    ->rules(fn (): array => AturanProfilLinktree::alamatPublik(auth()->id())),
                Textarea::make('bio')
                    ->label(__('ui.panel.deskripsi_singkat'))
                    ->rows(2)
                    ->placeholder(__('ui.panel.placeholder_bio'))
                    ->maxLength(500),
                Toggle::make('is_active')
                    ->label(__('ui.panel.terbitkan_halaman'))
                    ->helperText(__('ui.panel.helper_terbitkan')),
                Repeater::make('links')
                    ->label(__('ui.panel.daftar_tautan'))
                    ->addActionLabel(__('ui.panel.tambah_tautan'))
                    ->reorderableWithButtons()
                    ->itemLabel(fn (?array $state): ?string => $state['label'] ?? null)
                    ->schema([
                        TextInput::make('label')
                            ->label(__('ui.panel.judul_tautan'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label(__('ui.panel.alamat_tautan'))
                            ->required()
                            ->url()
                            ->maxLength(2048),
                        Toggle::make('is_active')
                            ->label(__('ui.panel.aktif'))
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = $this->service()->update(
            auth()->user(),
            UpdateLinktreeProfileDTO::fromArray($data),
        );

        $this->service()->syncLinks($profile, $this->linksFrom($data['links'] ?? []));

        Notification::make()->success()->title(__('ui.panel.tersimpan'))->send();
    }

    /**
     * Alamat halaman publik milik pengguna, null bila alamat publiknya belum diisi.
     */
    public function publicUrl(): ?string
    {
        $alamat = $this->service()->profile(auth()->user())->public_slug;

        return $alamat ? route('linktree.show', $alamat) : null;
    }

    private function service(): LinktreeService
    {
        return app(LinktreeService::class);
    }

    /**
     * Ubah baris-baris repeater menjadi DTO, urut sesuai tampilan di form.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, LinktreeLinkDTO>
     */
    private function linksFrom(array $rows): array
    {
        return array_map(
            fn (array $row): LinktreeLinkDTO => LinktreeLinkDTO::fromArray($row),
            array_values($rows),
        );
    }
}
