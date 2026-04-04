<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
