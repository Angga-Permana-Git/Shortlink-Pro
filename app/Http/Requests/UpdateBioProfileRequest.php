<?php

namespace App\Http\Requests;

use App\Support\Validation\AturanProfilBio;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Perubahan profil Bio lewat API. Aturan alamat publik dipakai bersama
 * dengan formulir panel pengguna supaya bentuknya sama di semua jalur.
 */
class UpdateBioProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'public_slug' => array_merge(
                ['sometimes'],
                AturanProfilBio::alamatPublik($this->user()?->id),
            ),
        ];
    }
}
