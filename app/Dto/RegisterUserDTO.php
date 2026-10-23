<?php

namespace App\Dto;

/**
 * Data pendaftaran akun lokal. Kata sandi masih mentah; peng-hash-an menjadi
 * tugas RegistrationService.
 */
final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $username,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            username: (string) $data['username'],
            email: (string) $data['email'],
            password: (string) $data['password'],
        );
    }
}
