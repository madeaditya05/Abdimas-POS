<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok',
        'harga',
        'kategori',
        'gambar',
        'deskripsi',
    ];

    public function reseps(): HasMany
    {
        return $this->hasMany(\App\Models\Resep::class, 'produk_id');
    }

    public function resepAktif(): HasOne
    {
        return $this->hasOne(\App\Models\Resep::class, 'produk_id')->where('is_active', 1);
    }

    public static function generateKodeBarang()
    {
        $last = self::orderBy('id', 'desc')->first();
        if (!$last || !$last->kode_barang) {
            $number = 1;
        } else {
            $number = (int)substr($last->kode_barang, 2) + 1;
        }
        return 'CF' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
