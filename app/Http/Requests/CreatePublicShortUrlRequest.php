<?php

namespace App\Http\Requests;

use App\Support\Validation\AturanTautanPendek;
use Illuminate\Foundation\Http\FormRequest;

class CreatePublicShortUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return AturanTautanPendek::semua();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return AturanTautanPendek::atribut();
    }
}
