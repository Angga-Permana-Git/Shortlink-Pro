<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Linktree\LinktreeAvatarService;
use App\Services\Linktree\LinktreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Logo profil Linktree: jenis berkas aman dan terpisah per pengguna.
 */
class LogoLinktreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_png_logo_is_saved_with_generated_safe_name(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $profil = app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.png', $this->png()));

        $this->assertStringStartsWith('linktree/'.$pengguna->getKey().'/', $profil->avatar_path);
        $this->assertStringEndsWith('.png', $profil->avatar_path);
        $this->assertStringNotContainsString('logo', $profil->avatar_path);
        Storage::disk('public')->assertExists($profil->avatar_path);
    }

    public function test_clean_svg_logo_is_accepted(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><circle cx="5" cy="5" r="4"/></svg>';

        $profil = app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.svg', $svg));

        $this->assertStringEndsWith('.svg', $profil->avatar_path);
        Storage::disk('public')->assertExists($profil->avatar_path);
    }

    public function test_svg_with_script_is_rejected(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $this->expectException(ValidationException::class);

        app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.svg', $svg));
    }

    public function test_php_file_disguised_as_png_is_rejected(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(LinktreeAvatarService::class)->simpan(
            $pengguna,
            $this->berkas('logo.png', '<?php echo "dijalankan"; ?>'),
        );
    }

    public function test_unsupported_extension_is_rejected(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.gif', 'GIF89a'));
    }

    public function test_logo_of_one_user_does_not_change_another_user(): void
    {
        Storage::fake('public');
        $penggunaA = User::factory()->create();
        $penggunaB = User::factory()->create();

        app(LinktreeAvatarService::class)->simpan($penggunaA, $this->berkas('logo.png', $this->png()));

        $this->assertNotNull(app(LinktreeService::class)->profile($penggunaA)->avatar_path);
        $this->assertNull(app(LinktreeService::class)->profile($penggunaB)->avatar_path);
    }

    public function test_old_logo_file_is_removed_when_replaced(): void
    {
        Storage::fake('public');
        $pengguna = User::factory()->create();

        $pertama = app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.png', $this->png()));
        $lama = $pertama->avatar_path;

        $kedua = app(LinktreeAvatarService::class)->simpan($pengguna, $this->berkas('logo.png', $this->png()));

        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($kedua->avatar_path);
    }

    /**
     * Berkas sungguhan (bukan tiruan) supaya jenis isinya benar-benar diperiksa.
     */
    private function berkas(string $nama, string $isi): UploadedFile
    {
        $lokasi = (string) tempnam(sys_get_temp_dir(), 'logo');
        file_put_contents($lokasi, $isi);

        return new UploadedFile($lokasi, $nama, null, null, true);
    }

    private function png(): string
    {
        return (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );
    }
}
