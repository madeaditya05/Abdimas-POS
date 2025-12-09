<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:255'],
            'stok'        => ['required', 'integer', 'min:0'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'kategori'    => ['required', 'string', 'max:50'],
            'gambar'      => ['nullable', 'image', 'max:2048'],
            'deskripsi'   => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_barang' => 'Nama Produk',
            'stok'        => 'Stok',
            'harga'       => 'Harga',
            'kategori'    => 'Kategori',
            'gambar'      => 'Gambar',
            'deskripsi'   => 'Deskripsi',
        ];
    }
}
