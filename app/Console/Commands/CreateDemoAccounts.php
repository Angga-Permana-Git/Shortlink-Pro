<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Enums\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDemoAccounts extends Command
{
    protected $signature = 'app:create-demo-accounts
                            {--password= : Kata sandi akun demo (default dari DEMO_ACCOUNT_PASSWORD)}
                            {--force : Izinkan perintah ini berjalan pada lingkungan produksi}';

    protected $description = 'Buat atau perbarui akun demo admin dan user untuk pengembangan';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Perintah ini diblokir pada lingkungan produksi. Tambahkan --force bila memang disengaja.');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: config('app.demo_account_password'));

        if ($password === '') {
            $password = Str::password(16);
            $this->warn('Kata sandi tidak ditentukan; kata sandi acak dibuat untuk sesi ini.');
        }

        if (mb_strlen($password) < 12) {
            $this->error('Kata sandi minimal 12 karakter.');

            return self::FAILURE;
        }

        $rows = [];

        foreach ($this->accounts() as $account) {
            $user = $this->resolveAccount($account);

            $user->fill([
                'name' => $account['name'],
                'username' => $account['username'],
                'email' => $account['email'],
                'password' => Hash::make($password),
                'auth_provider' => 'local',
                'role' => $account['role'],
                'is_protected' => false,
                'is_active' => true,
            ]);

            $user->save();

            $rows[] = [
                Role::from($account['role'])->label(),
                $account['email'],
                $account['username'],
                $password,
            ];
        }

        $this->newLine();
        $this->table(['Peran', 'Email', 'Username', 'Kata sandi'], $rows);
        $this->warn('Akun demo hanya untuk pengembangan. Ganti kata sandinya sebelum dipakai di produksi.');
        $this->line('Jalur masuk: /login lalu gunakan formulir login lokal.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{name: string, username: string, email: string, role: string}>
     */
    private function accounts(): array
    {
        return [
            [
                'name' => 'Administrator Demo',
                'username' => 'admin',
                'email' => 'admin@company.local',
                'role' => Role::Admin->value,
            ],
            [
                'name' => 'User Demo',
                'username' => 'user',
                'email' => 'user@company.local',
                'role' => Role::User->value,
            ],
        ];
    }

    /**
     * @param  array{name: string, username: string, email: string, role: string}  $account
     */
    private function resolveAccount(array $account): User
    {
        return User::query()
            ->where('username', $account['username'])
            ->orWhere('email', $account['email'])
            ->first() ?? new User;
    }
}
