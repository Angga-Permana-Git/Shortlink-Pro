<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateLocalAdmin extends Command
{
    protected $signature = 'app:create-local-admin';

    protected $description = 'Buat local super admin (break-glass account) untuk bootstrap/pemulihan';

    public function handle(): int
    {
        $username = strtolower(trim((string) $this->ask('Username')));
        $name = trim((string) $this->ask('Nama'));

        $password = (string) $this->secret('Password');
        $confirm = (string) $this->secret('Konfirmasi password');

        if ($password !== $confirm) {
            $this->error('Password dan konfirmasi tidak sama.');

            return self::FAILURE;
        }

        if (mb_strlen($password) < 12) {
            $this->error('Password minimal 12 karakter.');

            return self::FAILURE;
        }

        $validator = Validator::make([
            'username' => $username,
        ], [
            'username' => ['required', 'alpha_dash', Rule::unique('users', 'username')],
        ]);

        if ($validator->fails()) {
            $this->error('Username tidak valid atau sudah dipakai: '.$username);

            return self::FAILURE;
        }

        $user = User::create([
            'username' => $username,
            'name' => $name,
            'email' => $username.'@local',
            'password' => Hash::make($password),
            'auth_provider' => 'local',
            'role' => 'admin',
            'is_protected' => true,
            'is_active' => true,
        ]);

        $this->info("Local super admin '{$user->username}' berhasil dibuat.");

        return self::SUCCESS;
    }
}