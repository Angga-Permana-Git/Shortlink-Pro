<?php

namespace App\Services\Auth;

use App\Models\User;

/** TOTP (RFC 6238) tanpa dependensi tambahan untuk akun admin. */
class TwoFactorService
{
    private const ALFABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $panjang = 32): string
    {
        $secret = '';

        for ($i = 0; $i < $panjang; $i++) {
            $secret .= self::ALFABET[random_int(0, 31)];
        }

        return $secret;
    }

    public function otpauthUri(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) config('app.name', 'Shortlink Pro'));

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $issuer,
            rawurlencode($user->email),
            $secret,
            $issuer,
        );
    }

    public function verify(string $secret, string $kode, int $jendela = 1): bool
    {
        $kode = preg_replace('/\D/', '', $kode) ?? '';
        $kunci = $this->base32Decode($secret);

        if (strlen($kode) !== 6 || $kunci === '') {
            return false;
        }

        $waktu = (int) floor(time() / 30);

        for ($i = -$jendela; $i <= $jendela; $i++) {
            if (hash_equals($this->kodeUntuk($kunci, $waktu + $i), $kode)) {
                return true;
            }
        }

        return false;
    }

    public function kodeSaatIni(string $secret): string
    {
        $kunci = $this->base32Decode($secret);

        return $kunci === '' ? '' : $this->kodeUntuk($kunci, (int) floor(time() / 30));
    }

    private function kodeUntuk(string $kunci, int $waktu): string
    {
        $hash = hash_hmac('sha1', pack('N2', 0, $waktu), $kunci, true);
        $offset = ord($hash[19]) & 0x0F;
        $nilai = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($nilai % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function aktifkan(User $user, string $kode): bool
    {
        $secret = (string) session('admin_2fa_setup');

        if ($secret === '' || ! $this->verify($secret, $kode)) {
            return false;
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        session()->forget('admin_2fa_setup');

        return true;
    }

    public function nonaktifkan(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    private function base32Decode(string $secret): string
    {
        $biner = '';

        foreach (str_split(strtoupper($secret)) as $karakter) {
            $posisi = strpos(self::ALFABET, $karakter);

            if ($posisi === false) {
                continue;
            }

            $biner .= str_pad(decbin($posisi), 5, '0', STR_PAD_LEFT);
        }

        $hasil = '';

        foreach (str_split($biner, 8) as $byte) {
            if (strlen($byte) === 8) {
                $hasil .= chr(bindec($byte));
            }
        }

        return $hasil;
    }
}
