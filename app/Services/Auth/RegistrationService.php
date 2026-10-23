<?php

namespace App\Services\Auth;

use App\Dto\RegisterUserDTO;
use App\Models\User;
use App\Support\Enums\Role;
use Illuminate\Support\Facades\Hash;

/**
 * Pendaftaran akun lokal.
 *
 * Peng-hash-an kata sandi dilakukan di sini, bukan di controller
 * (disiplin_struktur.md bagian 1).
 */
class RegistrationService
{
    public function register(RegisterUserDTO $data): User
    {
        return User::create([
            'name' => $data->name,
            'username' => $data->username,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'auth_provider' => 'local',
            'role' => Role::User->value,
            'is_active' => true,
        ]);
    }
}
