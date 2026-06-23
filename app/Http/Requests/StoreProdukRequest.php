<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kalau nanti mau dibatasi per role, logic-nya bisa ditaruh di sini
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang'  => ['required', 'string', 'max:255'],
            'harga'        => ['required', 'numeric', 'min:0'],
            'harga_online' => ['required', 'numeric', 'min:0'],
            'kategori'     => ['required', 'string', 'max:50', Rule::exists('kategori_produk', 'slug')],
            'gambar'       => ['nullable', 'image', 'max:2048'], // ~2MB
            'deskripsi'    => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_barang'  => 'Nama Produk',
            'harga'        => 'Harga Offline',
            'harga_online' => 'Harga Online',
            'kategori'     => 'Kategori',
            'gambar'       => 'Gambar',
            'deskripsi'    => 'Deskripsi',
        ];
    }
}
