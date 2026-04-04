<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanBakuRequest extends FormRequest
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
        return (new StoreBahanBakuRequest())->rules();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'aktif'                  => (bool) $this->boolean('aktif'),
            'is_perishable'          => (bool) $this->boolean('is_perishable'),
            'kelola_expired'         => (bool) $this->boolean('kelola_expired'),
            'konversi_beli_ke_pakai' => $this->input('konversi_beli_ke_pakai', 1),
            'yield_persen'           => $this->input('yield_persen', 100),
            'penyimpanan'            => $this->input('penyimpanan', 'room'),
        ]);
    }
}
