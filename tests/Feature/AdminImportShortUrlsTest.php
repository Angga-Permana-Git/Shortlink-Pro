<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\ImportShortUrls;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminImportShortUrlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_valid_rows_and_see_failures(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pemilik = User::factory()->create(['role' => 'user', 'is_active' => true, 'email' => 'pemilik@example.com']);

        Storage::disk('local')->put('imports/tautan.csv', implode("\n", [
            'slug,destination_url,owner_email,is_active',
            'promo,https://example.com/promo,pemilik@example.com,true',
            'promo,https://example.com/duplikat,pemilik@example.com,true',
            ',https://example.com/biasa,,true',
        ]));

        Livewire::actingAs($admin)
            ->test(ImportShortUrls::class)
            ->fillForm(['file' => ['imports/tautan.csv']])
            ->call('import')
            ->assertSet('berhasil', 2)
            ->assertSet('gagal', fn (array $gagal): bool => count($gagal) === 1);

        $this->assertDatabaseHas('short_urls', [
            'slug' => 'promo',
            'owner_id' => $pemilik->id,
            'destination_url' => 'https://example.com/promo',
        ]);
        $this->assertSame(2, ShortUrl::count());
    }
}
