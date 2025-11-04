<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBahanBakuRequest extends FormRequest
{
    // /**
    //  * Determine if the user is authorized to make this request.
    //  */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    // /**
    //  * Get the validation rules that apply to the request.
    //  *
    //  * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    //  */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
             'nama_bahan'             => ['required','string','max:150'],
            'kategori'               => ['nullable','in:kopi,susu,bumbu,kemasan,lainnya'],
            'aktif'                  => ['boolean'],
            'satuan_pakai'           => ['required','in:pcs,gram,kg,ml,liter,pack,box'],
            'satuan_beli'            => ['nullable','in:pcs,gram,kg,ml,liter,pack,box'],
            'konversi_beli_ke_pakai' => ['nullable','numeric','min:0.001'],
            'isi_per_kemasan'        => ['nullable','numeric','min:0'],
            'penyimpanan'            => ['nullable','in:room,chiller,freezer'],
            'is_perishable'          => ['boolean'],
            'masa_simpan_hari'       => ['nullable','integer','min:0'],
            'kelola_expired'         => ['boolean'],
            'allergen_flag'          => ['nullable','in:none,gluten,dairy,nut,soy,egg'],
            'status_halal'           => ['nullable','in:halal,non_halal,unknown'],
            'default_supplier_nama'  => ['nullable','string','max:150'],
            'supplier_kontak'        => ['nullable','string','max:150'],
            'lead_time_hari'         => ['nullable','integer','min:0'],
            'min_order_qty'          => ['nullable','numeric','min:0'],
            'yield_persen'           => ['required','numeric','between:0,100'],
            'dipakai_di_resep'       => ['boolean'],
            'foto_path'              => ['nullable','image','max:2048'],
            'catatan'                => ['nullable','string'],
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'aktif'            => (bool) $this->boolean('aktif'),
            'is_perishable'    => (bool) $this->boolean('is_perishable'),
            'kelola_expired'   => (bool) $this->boolean('kelola_expired'),
            'dipakai_di_resep' => (bool) $this->boolean('dipakai_di_resep'),
            'konversi_beli_ke_pakai' => $this->input('konversi_beli_ke_pakai', 1),
            'yield_persen'           => $this->input('yield_persen', 100),
            'penyimpanan'            => $this->input('penyimpanan', 'room'),
        ]);
    }
}
