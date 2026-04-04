<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembelianBahanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {


        return [
        'tanggal'          => ['nullable', 'date'],
        'supplier_nama'    => ['nullable', 'string', 'max:255'],
        'supplier_kontak'  => ['nullable', 'string', 'max:255'],
        'catatan'          => ['nullable', 'string'],
        'bukti_file'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf'],
        'details'          => ['required', 'array'],
        ];
    }
}
