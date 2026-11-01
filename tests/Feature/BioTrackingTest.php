<?php

namespace Tests\Feature;

use App\Models\BioLink;
use App\Models\BioProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BioTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_kunjungan_dan_klik_halaman_publik_dicatat(): void
    {
        [$profil, $tautan] = $this->profilDanTautan();

        $this->get('/u/'.$profil->public_slug)->assertOk();
        $this->assertDatabaseHas('bio_events', [
            'bio_profile_id' => $profil->id,
            'jenis' => 'kunjungan',
        ]);

        $this->get(route('bio.click', [$profil->public_slug, $tautan->id]))
            ->assertRedirect('https://example.com/tujuan');

        $this->assertDatabaseHas('bio_events', [
            'bio_profile_id' => $profil->id,
            'bio_link_id' => $tautan->id,
            'jenis' => 'klik',
        ]);
    }

    public function test_tautan_nonaktif_tidak_mencatat_klik(): void
    {
        [$profil, $tautan] = $this->profilDanTautan(false);

        $this->get(route('bio.click', [$profil->public_slug, $tautan->id]))
            ->assertNotFound();

        $this->assertDatabaseMissing('bio_events', ['jenis' => 'klik']);
    }

    /**
     * @return array{0: BioProfile, 1: BioLink}
     */
    private function profilDanTautan(bool $aktif = true): array
    {
        $pengguna = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);
        $profil = BioProfile::create([
            'user_id' => $pengguna->id,
            'display_name' => 'Profil Uji',
            'public_slug' => 'profil-uji',
            'is_active' => true,
        ]);
        $tautan = BioLink::create([
            'profile_id' => $profil->id,
            'label' => 'Tujuan',
            'url' => 'https://example.com/tujuan',
            'is_active' => $aktif,
        ]);

        return [$profil, $tautan];
    }
}
