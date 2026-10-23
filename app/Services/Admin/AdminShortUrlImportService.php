<?php

namespace App\Services\Admin;

use App\Dto\CreateShortUrlDTO;
use App\Models\User;
use App\Services\ShortUrl\ShortUrlService;
use App\Support\Validation\AturanTautanPendek;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** Mengimpor tautan dari berkas CSV dengan validasi yang sama seperti formulir. */
class AdminShortUrlImportService
{
    private const BATAS_BARIS = 5000;

    /**
     * @return array{berhasil: int, gagal: array<int, string>}
     */
    public function import(string $path, User $actor): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Berkas CSV tidak dapat dibaca.');
        }

        $header = $this->header($handle);
        $wajib = ['destination_url'];

        foreach ($wajib as $kolom) {
            if (! in_array($kolom, $header, true)) {
                fclose($handle);

                throw new RuntimeException('Kolom wajib tidak ditemukan: '.$kolom.'.');
            }
        }

        $berhasil = 0;
        $gagal = [];
        $baris = 1;

        while (($row = fgetcsv($handle)) !== false && $baris < self::BATAS_BARIS) {
            $baris++;

            if (array_filter($row, fn ($nilai) => trim((string) $nilai) !== '') === []) {
                continue;
            }

            $data = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), null));

            try {
                $this->importBaris($data, $actor);
                $berhasil++;
            } catch (ValidationException $e) {
                $gagal[] = 'Baris '.$baris.': '.collect($e->errors())->flatten()->first();
            } catch (\Throwable $e) {
                $gagal[] = 'Baris '.$baris.': '.$e->getMessage();
            }
        }

        fclose($handle);

        return ['berhasil' => $berhasil, 'gagal' => $gagal];
    }

    /** @return array<int, string> */
    private function header($handle): array
    {
        $header = fgetcsv($handle);

        if ($header === false) {
            throw new RuntimeException('Berkas CSV kosong.');
        }

        $alias = ['url' => 'destination_url', 'email' => 'owner_email', 'alamat' => 'destination_url'];

        return array_map(function ($kolom) use ($alias): string {
            $nama = strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $kolom)));

            return $alias[$nama] ?? $nama;
        }, $header);
    }

    /** @param array<string, mixed> $data */
    private function importBaris(array $data, User $actor): void
    {
        $data['destination_url'] = (string) ($data['destination_url'] ?? '');
        $data['slug'] = ($data['slug'] ?? '') === '' ? null : (string) $data['slug'];
        $data['owner_email'] = ($data['owner_email'] ?? '') === '' ? null : (string) $data['owner_email'];
        $data['expires_at'] = ($data['expires_at'] ?? '') === '' ? null : (string) $data['expires_at'];

        Validator::make($data, AturanTautanPendek::semua(pindaiTujuan: false))
            ->validate();

        $data = Validator::make($data, [
            'owner_email' => ['nullable', 'email', 'exists:users,email'],
        ])->validate() + $data;

        $pemilik = $data['owner_email'] === null
            ? $actor
            : User::where('email', $data['owner_email'])->firstOrFail();

        DB::transaction(fn () => app(ShortUrlService::class)->create($pemilik, new CreateShortUrlDTO(
            destinationUrl: $data['destination_url'],
            customSlug: $data['slug'],
            isActive: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            expiresAt: $data['expires_at'],
        )));
    }
}
