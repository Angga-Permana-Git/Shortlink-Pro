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

    protected static ?string $navigationLabel = 'Linktree';

    protected static ?string $title = 'Halaman Linktree Saya';

    protected string $view = 'filament.user.pages.linktree-settings';

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
                    ->label('Nama yang tampil di halaman publik')
                    ->required()
                    ->maxLength(255),
                TextInput::make('public_slug')
                    ->label('Alamat publik')
                    ->helperText('Huruf kecil, angka, dan tanda hubung. Dipakai pada /u/{alamat}.')
                    ->prefix('u/')
                    ->required()
                    ->maxLength(60)
                    ->rules(fn (): array => AturanProfilLinktree::alamatPublik(auth()->id())),
                Textarea::make('bio')
                    ->label('Deskripsi singkat')
                    ->rows(2)
                    ->placeholder('cth: Penulis lepas yang suka berbagi tautan bermanfaat.')
                    ->maxLength(500),
                Toggle::make('is_active')
                    ->label('Terbitkan halaman publik')
                    ->helperText('Jika dimatikan, pengunjung tidak bisa membuka halaman Anda.'),
                Repeater::make('links')
                    ->label('Daftar tautan')
                    ->addActionLabel('Tambah tautan')
                    ->reorderableWithButtons()
                    ->itemLabel(fn (?array $state): ?string => $state['label'] ?? null)
                    ->schema([
                        TextInput::make('label')
                            ->label('Judul tautan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('Alamat tautan')
                            ->required()
                            ->url()
                            ->maxLength(2048),
                        Toggle::make('is_active')
                            ->label('Aktif')
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

        Notification::make()->success()->title('Halaman Linktree tersimpan')->send();
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
