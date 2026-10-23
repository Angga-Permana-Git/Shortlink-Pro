<?php

namespace App\Support\Auth;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Aturan username akun: nama yang tidak boleh dipakai siapa pun dan pembentukan
 * username unik dari email.
 *
 * Dipakai bersama oleh pembuatan akun dari penyedia identitas (Keycloak dan
 * Google) supaya daftar nama cadangan hanya ditulis sekali.
 */
class UsernameAkun
{
    /** Username yang tidak boleh dipakai akun mana pun. */
    public const CADANGAN = ['root', 'admin', 'superadmin', 'administrator'];

    /**
     * Username unik dari bagian depan email. Hanya huruf kecil, angka, garis
     * bawah, dan tanda hubung supaya lolos aturan alpha_dash; nama cadangan
     * dan nama yang sudah terpakai dihindari dengan akhiran angka.
     */
    public static function dariEmail(string $email): string
    {
        $dasar = trim(Str::slug(Str::before($email, '@'), '_'), '_-');
        $dasar = mb_substr($dasar, 0, 40);
        $dasar = trim($dasar, '_-');

        if ($dasar === '' || ctype_digit($dasar)) {
            $dasar = 'pengguna';
        }

        $kandidat = $dasar;
        $urutan = 2;

        while (self::terpakai($kandidat)) {
            $kandidat = $dasar.'-'.$urutan;
            $urutan++;
        }

        return $kandidat;
    }

    private static function terpakai(string $username): bool
    {
        return in_array($username, self::CADANGAN, true)
            || User::query()->where('username', $username)->exists();
    }
}
