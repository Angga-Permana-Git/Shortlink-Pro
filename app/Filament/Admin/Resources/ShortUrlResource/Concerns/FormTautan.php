<?php

namespace App\Filament\Admin\Resources\ShortUrlResource\Concerns;

use App\Models\ShortUrl;
use App\Support\Validation\AturanTautanPendek;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Formulir tambah/sunting tautan di panel admin.
 *
 * Dipisah dari ShortUrlResource supaya berkas resource tetap di bawah 150 baris
 * (disiplin_struktur.md bagian 2); perilakunya tidak berubah.
 */
trait FormTautan
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('slug')
                    ->required()
                    ->placeholder(__('cth: promo-2026'))
                    ->maxLength(AturanTautanPendek::PANJANG_SLUG_MAKS)
                    ->rule('regex:'.AturanTautanPendek::POLA_SLUG)
                    ->unique(ignoreRecord: true)
                    ->label(__('Nama Pendek')),
                TextInput::make('destination_url')
                    ->required()
                    ->rules(AturanTautanPendek::semua()['destination_url'])
                    ->placeholder(__('https://example.com/halaman-tujuan'))
                    ->label(__('URL Tujuan'))
                    ->columnSpanFull(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label(__('Pemilik')),
                Toggle::make('is_active')->default(true)->label(__('Aktif')),
                DateTimePicker::make('starts_at')->label(__('Mulai aktif')),
                DateTimePicker::make('expires_at')
                    ->label(__('Kedaluwarsa'))
                    ->rules(AturanTautanPendek::semua()['expires_at']),
                Toggle::make('remove_password')
                    ->label(__('Hapus proteksi kata sandi'))
                    ->live()
                    ->default(false),
                TextInput::make('new_password')
                    ->label(__('Atur kata sandi baru'))
                    ->password()
                    ->revealable()
                    ->rules(AturanTautanPendek::perubahan()['password'])
                    ->placeholder(__('Kosongkan untuk tetap memakai kata sandi lama'))
                    ->helperText(__('Isi untuk mengganti kata sandi lama. Kosongkan untuk mempertahankan kata sandi lama.')),
            ]);
    }

    /**
     * Menyiapkan kolom password_hash dari isian formulir, baik saat membuat
     * maupun menyunting tautan.
     */
    public static function siapkanKataSandi(array $data, ?ShortUrl $tautan = null): array
    {
        $hash = $tautan?->password_hash;

        if (! empty($data['remove_password'])) {
            $hash = null;
        }

        if (! empty($data['new_password'])) {
            $hash = Hash::make($data['new_password']);
        }

        $data['password_hash'] = $hash;

        unset($data['new_password'], $data['remove_password']);

        return $data;
    }
}
